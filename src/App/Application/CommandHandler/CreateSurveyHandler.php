<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\CreateSurveyCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Service\SurveyService;
use Ramsey\Uuid\Uuid;

class CreateSurveyHandler
{
    public function __construct(
        private SurveyService $surveyService
    ) {
    }

    public function __invoke(CreateSurveyCommand $command): Survey
    {
        return $this->surveyService->createSurvey(
            Uuid::fromString($command->getId()),
            $command->getName(),
            $command->getReportEmail(),
        );
    }
}