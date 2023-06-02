<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report;
use App\Entity\Survey;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

final class ReportGenerator
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
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
        $report->setQuality((int) ($sum / $counter));
        $report->setComments($comments);

        $this->managerRegistry->getRepository(Report::class)->save($report);

        return $report;
    }
}
