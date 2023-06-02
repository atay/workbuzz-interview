<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
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
