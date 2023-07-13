<?php

namespace App\Domain\Service;

use App\Domain\Model\Survey\Answer;
use App\Domain\Model\Survey\Report;
use App\Infrastructure\Framework\Symfony\Repository\AnswerRepository;
use App\Infrastructure\Framework\Symfony\Repository\AnswerWriteRepository;
use App\Infrastructure\Framework\Symfony\Repository\ReportRepository;
use App\Infrastructure\Framework\Symfony\Repository\SurveyRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AnswerService
{

    public function __construct(
        private AnswerWriteRepository $answerWriteRepository,
        private AnswerRepository $answerRepository,
        private SurveyRepository $surveyRepository,

    ) {
    }

    public function addAnswer(UuidInterface $id, string $quality, ?string $comment): Answer
    {
        $survey = $this->surveyRepository->find($id);
        $answer = new Answer();
        $answer->setId(Uuid::uuid4());
        $answer->setQuality($quality);
        $answer->setComment($comment);
        $answer->setSurvey($survey);

        $this->answerWriteRepository->save($answer, true);

        return $answer;
    }

}