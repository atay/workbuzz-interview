<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Model\Survey\Report;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ReportMailer
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function send(Report $report): void
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to($report->getSurvey()->getReportEmail())
            ->subject(sprintf('Report for survey "%s" is here!', $report->getSurvey()->getName()))
            ->text(
                sprintf(
                    'There should be a link to generated report but just id is also fine ;) - "%s"',
                    $report->getId(),
                ),
            );

        $this->mailer->send($email);
    }
}