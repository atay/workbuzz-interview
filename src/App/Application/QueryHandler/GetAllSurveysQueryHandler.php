<?php

namespace App\Application\QueryHandler;

use App\Application\DTO\SurveyDto;
use App\Domain\Model\Survey\Survey;
use App\Domain\Query\GetAllSurveysQuery;
use App\Domain\Repository\SurveyRepositoryInterface;

class GetAllSurveysQueryHandler
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {
    }

    public function __invoke(GetAllSurveysQuery $query): array
    {
        $surveys = $this->surveyRepository->findAll();

        return array_map(
            fn(Survey $survey) => SurveyDto::fromSurvey($survey),
            $surveys
        );
    }
}