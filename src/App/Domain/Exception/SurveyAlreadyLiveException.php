<?php

namespace App\Domain\Exception;

class SurveyAlreadyLiveException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Survey is already live, no changes were made');
    }
}