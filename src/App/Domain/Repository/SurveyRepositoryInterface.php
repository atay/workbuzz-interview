<?php

namespace App\Domain\Repository;

interface SurveyRepositoryInterface
{
    public function find($id, $lockMode = null, $lockVersion = null);
}