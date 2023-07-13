<?php

namespace App\Application\CommandHandler;

use App\Domain\Command\GenerateReportCommand;
use App\Domain\Service\ReportService;
use Symfony\Component\Messenger\MessageBusInterface;

class GenerateReportHandler
{
    public function __construct(
        private ReportService $reportService,
    ) {
    }

    public function __invoke(GenerateReportCommand $command): void
    {
        $this->reportService->generate($command->getId());

    }
}