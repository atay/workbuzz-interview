<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Infrastructure\Framework\Symfony\Form\AnswerType;
use App\Infrastructure\Framework\Symfony\Security\Voter\SurveyVoter;
use App\Domain\Model\Survey\Answer;
use App\Domain\Model\Survey\Survey;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AnswerController extends AbstractController
{
    #[Route('/survey/{id}/answer', methods: 'POST')]
    #[ParamConverter('survey', Survey::class)]
    public function create(Survey $survey, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(SurveyVoter::ANSWER, $survey);

        $answer = new Answer();
        $answer->setId(Uuid::uuid4());

        $form = $this->createForm(AnswerType::class, $answer);
        $form->submit(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));

        if ($form->isSubmitted() && $form->isValid()) {
            $survey->addAnswer($answer);
            $this->getDoctrine()->getRepository(Survey::class)->save($survey, true);
        } else {
            return $this->json($form);
        }

        return $this->json($survey);
    }
}