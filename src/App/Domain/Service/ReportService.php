<?php

namespace App\Domain\Service;

use App\Domain\Command\SendReportCommand;
use App\Domain\Model\Survey\Report;
use App\Infrastructure\Framework\Symfony\Repository\ReportRepository;
use App\Infrastructure\Framework\Symfony\Repository\ReportWriteRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ReportService
{

    public function __construct(
        private ReportWriteRepository $reportWriteRepository,
        private ReportRepository $reportRepository,
        private SurveyService $surveyService,
        private MessageBusInterface $messageBus,
    ) {
    }
    public function generate(UuidInterface $id): Report
    {
        $survey = $this->surveyService->find($id);

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

        $this->reportWriteRepository->save($report, true);

        $this->messageBus->dispatch(
            new SendReportCommand($report->getId()),
        );

        return $report;
    }

    public function find(UuidInterface $id): ?Report
    {
        return $this->reportRepository->find($id);
    }
}