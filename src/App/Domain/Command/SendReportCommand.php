<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class SendReportCommand
{
    public function __construct(
        private UuidInterface $id,
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}