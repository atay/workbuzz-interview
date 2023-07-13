<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\DeleteSurveyCommand;
use App\Domain\Command\SaveSurveyCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Service\SurveyService;
use Ramsey\Uuid\Uuid;

class DeleteSurveyHandler
{
    public function __construct(
        private SurveyService $surveyService
    ) {
    }

    public function __invoke(DeleteSurveyCommand $command): void
    {
        $this->surveyService->deleteSurvey(
            $command->getId(),
        );
    }
}