<?php

namespace App\Domain\Service;

use App\Domain\Command\GenerateReportCommand;
use App\Domain\Exception\SurveyAlreadyLiveException;
use App\Domain\Exception\SurveyClosedException;
use App\Domain\Exception\SurveySameStatusException;
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
        $survey = $this->surveyRepository->find($id);
        $this->surveyWriteRepository->remove($survey, true);
    }

    public function find(UuidInterface $id): ?Survey
    {
        return $this->surveyRepository->find($id);
    }

    public function changeStatus(UuidInterface $id, string $newStatus): Survey
    {
        $survey = $this->surveyRepository->find($id);
        $status = $survey->getStatus();
        if ($status === $newStatus) {
            throw new SurveySameStatusException();
        }
        if ($status === Survey::STATUS_CLOSED) {
            throw new SurveyClosedException();
        }
        if (
            $status === Survey::STATUS_LIVE
            && $newStatus === Survey::STATUS_NEW
        ) {
            throw new SurveyAlreadyLiveException();
        }
        $survey->setStatus($newStatus);
        $this->surveyWriteRepository->save($survey, true);

        if ($newStatus === Survey::STATUS_CLOSED) {
            $this->messageBus->dispatch(
                new GenerateReportCommand($id),
            );
        }

        return $survey;
    }

}