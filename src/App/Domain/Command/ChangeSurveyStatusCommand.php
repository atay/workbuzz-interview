<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class ChangeSurveyStatusCommand
{
    public function __construct(
        private UuidInterface $id,
        private string $status,
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}