<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class AddAnswerCommand
{
    public function __construct(
        private UuidInterface $surveyId,
        private int $quality,
        private ?string $comment = null,
    ) {
    }

    public function getSurveyId(): UuidInterface
    {
        return $this->surveyId;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }


}