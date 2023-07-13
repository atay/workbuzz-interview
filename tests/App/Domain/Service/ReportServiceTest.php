<?php

namespace App\Tests\Domain\Service;

use App\Domain\Command\SendReportCommand;
use App\Domain\Model\Survey\Report;
use App\Domain\Model\Survey\Survey;
use App\Domain\Service\ReportService;
use App\Domain\Service\SurveyService;
use App\Infrastructure\Framework\Symfony\Repository\ReportRepository;
use App\Infrastructure\Framework\Symfony\Repository\ReportWriteRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class ReportServiceTest extends TestCase
{
    private $reportWriteRepository;
    private $reportRepository;
    private $surveyService;
    private $messageBus;
    private $reportService;

    protected function setUp(): void
    {
        $this->reportWriteRepository = $this->createMock(ReportWriteRepository::class);
        $this->reportRepository = $this->createMock(ReportRepository::class);
        $this->surveyService = $this->createMock(SurveyService::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->reportService = new ReportService(
            $this->reportWriteRepository,
            $this->reportRepository,
            $this->surveyService,
            $this->messageBus
        );
    }

    public function testGenerate(): void
    {
        $id = Uuid::uuid4();
        $survey = new Survey();
        $this->surveyService->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($survey);

        $this->reportWriteRepository->expects($this->once())
            ->method('save');

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SendReportCommand::class))
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()));

        $report = $this->reportService->generate($id);

        $this->assertInstanceOf(Report::class, $report);
    }


    public function testFind(): void
    {
        $id = Uuid::uuid4();
        $report = new Report();
        $this->reportRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($report);

        $result = $this->reportService->find($id);

        $this->assertSame($report, $result);
    }
}