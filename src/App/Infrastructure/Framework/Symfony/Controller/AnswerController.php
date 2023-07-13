<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Application\DTO\SurveyDto;
use App\Domain\Command\AddAnswerCommand;
use App\Domain\Security\Voter\SurveyVoter;
use App\Domain\Service\AnswerService;
use App\Domain\Service\SurveyService;
use App\Infrastructure\Framework\Symfony\Form\AnswerType;
use App\Domain\Model\Survey\Answer;
use App\Domain\Model\Survey\Survey;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class AnswerController extends AbstractController
{

    public function __construct(
        private AnswerService $answerService,
        private SurveyService $surveyService,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/survey/{id}/answer', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $survey = $this->surveyService->find(Uuid::fromString($request->get('id')));
        $answer = new Answer();

        $form = $this->createForm(AnswerType::class, $answer);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json($form);
        }

        try {
            $this->denyAccessUnlessGranted(SurveyVoter::ANSWER, $survey);
        } catch (\Throwable $th) {
            return $this->json(['error' => $th->getMessage()], 403);
        }

        $this->messageBus->dispatch(
            new AddAnswerCommand(
                $survey->getId(),
                $form->getData()->getQuality(),
                $form->getData()->getComment(),
            )
        );

        return $this->json(SurveyDto::fromSurvey($survey));
    }
}