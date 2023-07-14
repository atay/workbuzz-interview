<?php

namespace App\Domain\Repository;

use App\Domain\Model\Survey\Report;

interface ReportWriteRepositoryInterface
{
    public function save(Report $entity, bool $flush = false): void;
}