<?php

namespace App\Tests\Domain\Service;

use App\Domain\Command\GenerateReportCommand;
use App\Domain\Model\Survey\Survey;
use App\Domain\Repository\SurveyWriteRepositoryInterface;
use App\Domain\Service\SurveyService;
use App\Infrastructure\Framework\Symfony\Repository\SurveyRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class SurveyServiceTest extends TestCase
{
    private $surveyWriteRepository;
    private $surveyRepository;
    private $messageBus;
    private $surveyService;

    protected function setUp(): void
    {
        $this->surveyWriteRepository = $this->createMock(SurveyWriteRepositoryInterface::class);
        $this->surveyRepository = $this->getMockBuilder(SurveyRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->surveyService = new SurveyService(
            $this->surveyWriteRepository,
            $this->surveyRepository,
            $this->messageBus
        );
    }

    public function testCreateSurvey(): void
    {
        $id = Uuid::uuid4();
        $name = 'Test Survey';
        $reportEmail = 'test@example.com';

        $survey = new Survey();
        $survey->setId($id);
        $survey->setName($name);
        $survey->setReportEmail($reportEmail);
        $survey->setStatus(Survey::STATUS_NEW);

        $this->surveyWriteRepository->expects($this->once())
            ->method('save')
            ->with($survey, true);

        $survey = $this->surveyService->createSurvey($id, $name, $reportEmail);

        $this->assertInstanceOf(Survey::class, $survey);
        $this->assertEquals($id, $survey->getId());
        $this->assertEquals($name, $survey->getName());
        $this->assertEquals($reportEmail, $survey->getReportEmail());
    }

    public function testDeleteSurvey(): void
    {
        $id = Uuid::uuid4();

        $this->surveyRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(new Survey());

        $this->surveyWriteRepository->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Survey::class), true);

        $this->surveyService->deleteSurvey($id);
    }

    public function testFind(): void
    {
        $id = Uuid::uuid4();

        $this->surveyRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(new Survey());

        $survey = $this->surveyService->find($id);

        $this->assertInstanceOf(Survey::class, $survey);
    }

    public function testChangeStatus(): void
    {
        $id = Uuid::uuid4();
        $status = Survey::STATUS_CLOSED;

        $survey = new Survey();
        $survey->setStatus(Survey::STATUS_NEW);

        $this->surveyRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($survey);

        $this->surveyWriteRepository->expects($this->once())
            ->method('save')
            ->with($survey, true);

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(GenerateReportCommand::class))
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()));

        $result = $this->surveyService->changeStatus($id, $status);

        $this->assertInstanceOf(Survey::class, $result);
        $this->assertEquals($status, $result->getStatus());
    }



}