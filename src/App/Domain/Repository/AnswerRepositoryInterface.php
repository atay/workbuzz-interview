<?php

namespace App\Domain\Repository;

interface AnswerRepositoryInterface
{
    public function find($id, $lockMode = null, $lockVersion = null);
}