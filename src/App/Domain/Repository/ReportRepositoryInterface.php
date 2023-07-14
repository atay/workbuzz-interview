<?php

namespace App\Domain\Repository;

use App\Domain\Model\Survey\Report;

interface ReportRepositoryInterface
{
    public function find($id, $lockMode = null, $lockVersion = null);
}