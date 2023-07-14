<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Application\DTO\SurveyDto;
use App\Domain\Command\ChangeSurveyStatusCommand;
use App\Domain\Command\DeleteSurveyCommand;
use App\Domain\Command\SaveSurveyCommand;
use App\Domain\Exception\SurveyClosedException;
use App\Domain\Model\Survey\Survey;
use App\Domain\Query\GetAllSurveysQuery;
use App\Domain\Security\Voter\SurveyVoter;
use App\Domain\Service\SurveyService;
use App\Infrastructure\Framework\Symfony\Exception\WrongInputException;
use App\Infrastructure\Framework\Symfony\Form\StatusType;
use App\Infrastructure\Framework\Symfony\Form\SurveyType;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/survey')]
class SurveyController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SurveyService $surveyService,
    ) {
    }

    #[Route(methods: 'GET')]
    public function index(): JsonResponse
    {
        $query = new GetAllSurveysQuery();

        $envelope = $this->messageBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $surveys = $handledStamp->getResult();

        return $this->json($surveys);
    }

    #[Route(methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        return $this->createOrUpdate($request);
    }

    private function createOrUpdate(Request $request): JsonResponse
    {
        try {
            $survey = $this->processAndValidateInput($request);
        } catch (WrongInputException $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ], 400);
        }

        $this->messageBus->dispatch(
            new SaveSurveyCommand(
                $survey->getId(),
                $survey->getName(),
                $survey->getReportEmail(),
            )
        );

        return $this->json(SurveyDto::fromSurvey($survey));
    }

    private function processAndValidateInput(Request $request): Survey
    {
        $survey = new Survey();
        $survey->setId(Uuid::uuid4());

        $form = $this->createForm(SurveyType::class, $survey);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new WrongInputException($this->getFormErrorsAsArray($form));
        }

        return $survey;
    }

    private function getFormErrorsAsArray(FormInterface $form): array
    {
        if ($form === null) {
            return [];
        }
        $errors = $form->getErrors(true, true);
        $details = [];
        foreach ($errors as $error) {
            $details[$error->getOrigin()->getName()] = $error->getMessage();
        }
        return $details;
    }

    #[Route('/{id}', methods: 'PUT')]
    public function edit(Request $request): JsonResponse
    {

        $survey = $this->surveyService->find(Uuid::fromString($request->get('id')));
        if ($survey === null) {
            return $this->json(['error' => 'Survey not found'], 404);
        }
        try {
            $this->denyAccessUnlessGranted(SurveyVoter::EDIT, $survey);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }

        return $this->createOrUpdate($request);
    }

    #[Route('/{id}', methods: 'DELETE')]
    public function delete(Request $request): JsonResponse
    {
        $survey = $this->surveyService->find(Uuid::fromString($request->get('id')));
        if ($survey === null) {
            return $this->json(['error' => 'Survey not found'], 404);
        }

        try {
            $this->denyAccessUnlessGranted(SurveyVoter::DELETE, $survey);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }

        $this->messageBus->dispatch(
            new DeleteSurveyCommand($survey->getId()),
        );

        return $this->json([]);
    }



    #[Route('/{id}/status', methods: 'PUT')]
    public function changeStatus(Request $request): JsonResponse
    {
        $survey = $this->surveyService->find(Uuid::fromString($request->get('id')));
        if ($survey === null) {
            return $this->json(['error' => 'Survey not found'], 404);
        }

        $form = $this->createForm(StatusType::class);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json($form);
        }

        $status = $form->getData()['status'];
        try {
            $this->messageBus->dispatch(
                new ChangeSurveyStatusCommand($survey->getId(), $status),
            );
        } catch (HandlerFailedException $e) {
            $previousException = $e->getPrevious();
            if ($previousException instanceof \DomainException) {
                return $this->json(['error' => $previousException->getMessage()], 400);
            }
            throw $e;
        }

        return $this->json(SurveyDto::fromSurvey($survey));
    }
}