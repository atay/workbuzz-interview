<?php

namespace App\Infrastructure\Framework\Symfony\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WrongInputException extends BadRequestHttpException
{
    public array $details = [];

    public function __construct(array $details)
    {
        $this->details = $details;

        parent::__construct("Form validation error");
    }

    public function getDetails(): mixed
    {
        return $this->details;
    }
}