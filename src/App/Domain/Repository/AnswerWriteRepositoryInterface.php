<?php

namespace App\Domain\Repository;

use App\Domain\Model\Survey\Answer;

interface AnswerWriteRepositoryInterface
{
    public function save(Answer $entity, bool $flush = false): void;
}