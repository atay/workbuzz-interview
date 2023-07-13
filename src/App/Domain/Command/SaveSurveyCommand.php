<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class SaveSurveyCommand
{
    public function __construct(
        private UuidInterface $id,
        private string $name,
        private string $reportEmail,
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReportEmail(): string
    {
        return $this->reportEmail;
    }
}