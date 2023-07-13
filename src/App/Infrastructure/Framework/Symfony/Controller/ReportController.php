<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Symfony\Controller;

use App\Infrastructure\Persistence\Doctrine\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/report/{id}', methods: 'GET')]
    #[ParamConverter('report', Report::class)]
    public function show(Report $report): JsonResponse
    {
        return $this->json($report);
    }
}