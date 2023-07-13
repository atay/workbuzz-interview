<?php

namespace App\Domain\Repository;

use App\Domain\Model\Survey\Survey;

interface SurveyWriteRepositoryInterface
{
    public function save(Survey $entity, bool $flush = false): void;
    public function remove(Survey $entity, bool $flush = false): void;

}