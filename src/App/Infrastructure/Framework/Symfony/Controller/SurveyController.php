<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Application\DTO\SurveyDto;
use App\Domain\Command\CreateSurveyCommand;
use App\Domain\Service\SurveyService;
use App\Infrastructure\Framework\Symfony\Form\StatusType;
use App\Infrastructure\Framework\Symfony\Form\SurveyType;
use App\Infrastructure\Framework\Symfony\Security\Voter\SurveyVoter;
use App\Domain\Model\Survey\Survey;
use App\Service\ReportGenerator;
use App\Service\ReportMailer;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
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
        return $this->json($this->getDoctrine()->getRepository(Survey::class)->findAll());
    }

    #[Route(methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $survey = new Survey();
        $survey->setId(Uuid::uuid4());

        $form = $this->createForm(SurveyType::class, $survey);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json($form);
        }

        $this->messageBus->dispatch(
            new CreateSurveyCommand(
                $survey->getId()->__toString(),
                $survey->getName(),
                $survey->getReportEmail(),
            )
        );

        return $this->json(SurveyDto::fromSurvey($survey));



    }

    #[Route('/{id}', methods: 'PUT')]
    #[ParamConverter('survey', Survey::class)]
    public function edit(Survey $survey, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(SurveyVoter::EDIT, $survey);

        return $this->handleRequest($survey, $request);
    }

    #[Route('/{id}', methods: 'DELETE')]
    #[ParamConverter('survey', Survey::class)]
    public function delete(Survey $survey): JsonResponse
    {
        $this->denyAccessUnlessGranted(SurveyVoter::DELETE, $survey);

        $this->getDoctrine()->getRepository(Survey::class)->remove($survey, true);
        return $this->json([]);
    }

    private function handleRequest(Survey $survey, Request $request): JsonResponse
    {


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