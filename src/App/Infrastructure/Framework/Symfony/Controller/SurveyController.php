<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Application\DTO\SurveyDto;
use App\Domain\Command\SaveSurveyCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Query\GetAllSurveysQuery;
use App\Infrastructure\Framework\Symfony\Exception\WrongInputException;
use App\Infrastructure\Framework\Symfony\Form\StatusType;
use App\Infrastructure\Framework\Symfony\Form\SurveyType;
use App\Infrastructure\Framework\Symfony\Security\Voter\SurveyVoter;
use App\Service\ReportGenerator;
use App\Service\ReportMailer;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/survey')]
class SurveyController extends AbstractController
{
    public function __construct(
        private readonly ReportGenerator $reportGenerator,
        private readonly ReportMailer $reportMailer,
        private MessageBusInterface $messageBus,
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
        $errors = $form->getErrors(true, true);
        $details = [];
        foreach ($errors as $error) {
            $details[$error->getOrigin()->getName()] = $error->getMessage();
        }
        return $details;
    }

    #[Route('/{id}', methods: 'PUT')]
    #[ParamConverter('survey', Survey::class)]
    public function edit(Survey $survey, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(SurveyVoter::EDIT, $survey);

        $this->createOrUpdate($request);
    }

    #[Route('/{id}', methods: 'DELETE')]
    #[ParamConverter('survey', Survey::class)]
    public function delete(Survey $survey): JsonResponse
    {
        $this->denyAccessUnlessGranted(SurveyVoter::DELETE, $survey);

        $this->getDoctrine()->getRepository(Survey::class)->remove($survey, true);
        return $this->json([]);
    }

    #[Route('/{id}/status', methods: 'PUT')]
    #[ParamConverter('survey', Survey::class)]
    public function changeStatus(Survey $survey, Request $request): JsonResponse
    {
        $form = $this->createForm(StatusType::class);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        if ($form->isSubmitted() && $form->isValid()) {
            $survey->setStatus($form->getData()['status']);


            if ($survey->getStatus() === Survey::STATUS_CLOSED) {
                $report = $this->reportGenerator->generate($survey);
                $this->reportMailer->send($report);
            }

            $this->getDoctrine()->getRepository(Survey::class)->save($survey, true);
        } else {
            return $this->json($form);
        }

        return $this->json($survey);
    }
}