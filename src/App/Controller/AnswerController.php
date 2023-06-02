<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Security\Voter\SurveyVoter;
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
