<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Domain\Service\ReportService;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{

    public function __construct(
        private ReportService $reportService,
    ) {
    }

    #[Route('/report/{id}', methods: 'GET')]
    public function show(Request $request): JsonResponse
    {
        $report = $this->reportService->find(Uuid::fromString($request->get('id')));
        if (!$report) {
            return $this->json(['error' => 'Report not found'], 404);
        }

        return $this->json($report);
    }
}