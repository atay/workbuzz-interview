<?php

namespace App\Domain\Command;

use Ramsey\Uuid\Uuid;

class CreateSurveyCommand
{
    public function __construct(
        private ?string $id = null,
        private ?string $name = null,
        private ?string $reportEmail = null,
    ) {
    }

    public function getId(): string
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