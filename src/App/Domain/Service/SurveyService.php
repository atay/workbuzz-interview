<?php

namespace App\Domain\Service;

use App\Domain\Command\GenerateReportCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Repository\SurveyRepositoryInterface;
use App\Domain\Repository\SurveyWriteRepositoryInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SurveyService
{

    public function __construct(
        private SurveyWriteRepositoryInterface $surveyWriteRepository,
        private SurveyRepositoryInterface $surveyRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function createSurvey(UuidInterface $id, string $name, string $reportEmail): Survey
    {
        $survey = new Survey();
        $survey->setStatus(Survey::STATUS_NEW);

        $survey->setId($id);
        $survey->setName($name);
        $survey->setReportEmail($reportEmail);

        $this->surveyWriteRepository->save($survey, true);

        return $survey;
    }

    public function deleteSurvey(UuidInterface $id): void
    {
        $survey = $this->surveyWriteRepository->find($id);
        $this->surveyWriteRepository->remove($survey, true);
    }

    public function find(UuidInterface $id): ?Survey
    {
        return $this->surveyRepository->find($id);
    }

    public function changeStatus(UuidInterface $id, string $status): Survey
    {
        $survey = $this->surveyRepository->find($id);
        if ($survey->getStatus() === $status) {
            return $survey;
        }
        $survey->setStatus($status);
        $this->surveyWriteRepository->save($survey, true);

        if ($status === Survey::STATUS_CLOSED) {
            $this->messageBus->dispatch(
                new GenerateReportCommand($survey->getId()),
            );
        }

        return $survey;
    }

}