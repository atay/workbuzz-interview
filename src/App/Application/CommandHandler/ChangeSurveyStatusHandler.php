<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\ChangeSurveyStatusCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Service\SurveyService;

class ChangeSurveyStatusHandler
{
    public function __construct(
        private SurveyService $surveyService,
    ) {
    }

    public function __invoke(ChangeSurveyStatusCommand $command): Survey
    {
        return $this->surveyService->changeStatus(
            $command->getId(),
            $command->getStatus(),
        );

    }
}