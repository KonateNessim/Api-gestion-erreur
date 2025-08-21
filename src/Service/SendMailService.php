<?php

namespace App\Service;

use App\Entity\ErrorTicket;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendMailService
{
    private $mailer;
    private string $emailSender;
    private string $emailReceiver;


    public function __construct(MailerInterface $mailer,
     private    ReportGeneratorService $reportGenerator,
     string $emailSender,
        string $emailReceiver
     )
    {
        $this->mailer = $mailer;
         $this->emailSender = $emailSender;
        $this->emailReceiver = $emailReceiver;

        //dd($this->emailSender);
    }

    public function send(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $context = [],
        array $attachments = []
    ): void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        foreach ($attachments as $attachment) {
            $email->attachFromPath($attachment['path'], $attachment['filename']);
        }
        $this->mailer->send($email);
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                unlink($attachment['path']);
            }
        }
    }

    public function handleCriticalErrorNotification(
        ErrorTicket $error,
    ): void {

        if ($error->getPriority() === 1) {
            $context = [
                'error' => $error,
                'date' => new \DateTime()
            ];

            $attachments = [];

            $excelReport = $this->reportGenerator->safeGenerateExcelReport($error);
            if ($excelReport) {
                $attachments[] = [
                    'path' => $excelReport,
                    'filename' => sprintf('error_report_%d.xlsx', $error->getId())
                ];
            }

            $wordReport = $this->reportGenerator->safeGenerateWordReport($error);
            if ($wordReport) {
                $attachments[] = [
                    'path' => $wordReport,
                    'filename' => sprintf('error_report_%d.docx', $error->getId())
                ];
            }

            $this->send(
                $this->emailSender,
                $this->emailReceiver,
                '🚨 Erreur Critique #' . $error->getId(),
                'emails/critical_error.html.twig',
                $context,
                $attachments
            );
        }
    }
}
