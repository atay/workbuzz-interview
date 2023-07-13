<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\SendReportCommand;
use App\Domain\Service\ReportMailer;
use App\Infrastructure\Framework\Symfony\Repository\ReportRepository;

class SendReportCommandHandler
{
    public function __construct(
        private ReportMailer $reportMailer,
        private ReportRepository $reportRepository,
    ) {
    }

    public function __invoke(SendReportCommand $command): void
    {
        $report = $this->reportRepository->find($command->getId());
        $this->reportMailer->send($report);
    }
}