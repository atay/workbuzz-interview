<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\AddAnswerCommand;
use App\Domain\Model\Survey\Answer;
use App\Domain\Service\AnswerService;

class AddAnswerHandler
{
    public function __construct(
        private AnswerService $answerService,
    ) {
    }

    public function __invoke(AddAnswerCommand $command): Answer
    {
        return $this->answerService->addAnswer(
            $command->getSurveyId(),
            $command->getQuality(),
            $command->getComment(),
        );

    }
}