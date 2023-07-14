<?php

namespace App\Domain\Exception;

class SurveyClosedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Survey is closed');
    }
}