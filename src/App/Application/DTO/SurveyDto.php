<?php

namespace App\Application\DTO;

use App\Domain\Model\Survey\Survey;

class SurveyDto
{

    static public function fromSurvey(Survey $survey): self
    {
        return new self(
            $survey->getId(),
            $survey->getName(),
            $survey->getReportEmail(),
        );
    }

    public function __construct(
        private ?string $id = null,
        private ?string $name = null,
        private ?string $reportEmail = null,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getReportEmail(): ?string
    {
        return $this->reportEmail;
    }
}