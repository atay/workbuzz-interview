<?php

namespace App\Domain\Exception;

class SurveySameStatusException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Survey has this status already, no changes were made');
    }
}