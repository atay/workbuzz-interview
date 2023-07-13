<?php

namespace App\Domain\Service;

use App\Domain\Model\Survey\Survey;
use App\Domain\Repository\SurveyWriteRepositoryInterface;

use Ramsey\Uuid\UuidInterface;

class SurveyService
{

    public function __construct(
        private SurveyWriteRepositoryInterface $surveyWriteRepository
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
}