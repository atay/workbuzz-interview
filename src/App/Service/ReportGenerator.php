<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Model\Survey\Report;
use App\Domain\Model\Survey\Survey;
use App\Infrastructure\Framework\Symfony\Repository\ReportRepository;
use Ramsey\Uuid\Uuid;

final class ReportGenerator
{
    public function __construct(
        private ReportRepository $reportRepository,
    ) {
    }

    public function generate(Survey $survey): Report
    {
        $sum = 0;
        $counter = 0;
        $comments = [];
        foreach ($survey->getAnswers() as $answer) {
            $sum += (int) $answer->getQuality();
            if ($answer->getComment() !== null) {
                $comments[] = $answer->getComment();
            }

            $counter++;
        }

        $report = new Report();
        $report->setId(Uuid::uuid4());
        $report->setNumberOfAnswers($counter);
        $report->setSurvey($survey);
        $report->setGeneratedAt(new \DateTimeImmutable());
        $report->setQuality($counter > 0 ? (int) ($sum / $counter) : 0);
        $report->setComments($comments);

        $this->reportRepository->save($report, true);

        return $report;
    }
}