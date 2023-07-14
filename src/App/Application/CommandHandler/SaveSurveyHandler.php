<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\SaveSurveyCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Service\SurveyService;

class SaveSurveyHandler
{
    public function __construct(
        private SurveyService $surveyService
    ) {
    }

    public function __invoke(SaveSurveyCommand $command): Survey
    {
        return $this->surveyService->saveSurvey(
            $command->getId(),
            $command->getName(),
            $command->getReportEmail(),
        );
    }
}