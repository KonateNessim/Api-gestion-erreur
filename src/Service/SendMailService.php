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


    public function __construct(
        MailerInterface $mailer,
        private    ReportGeneratorService $reportGenerator,
        string $emailSender,
        string $emailReceiver
    ) {
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

    public function handleCriticalErrorNotification(ErrorTicket $error): void
    {
        if ($error->getPriority() !== 1) {
            return;
        }

        $attachments = [];
        $generatedFiles = [];

        try {
            $context = [
                'error' => $error,
                'date' => new \DateTime(),
                'attachments_generated' => false
            ];
            $excelReport = $this->reportGenerator->safeGenerateExcelReport($error);
            if ($excelReport && file_exists($excelReport)) {
                $excelFilename = sprintf(
                    'error_report_%d_%s.xlsx',
                    $error->getId(),
                    $error->getDate()->format('Ymd_His')
                );

                $attachments[] = [
                    'path' => $excelReport,
                    'filename' => $excelFilename,
                    'contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];
                $generatedFiles[] = $excelReport;
                $context['attachments_generated'] = true;
            }
            $wordReport = $this->reportGenerator->safeGenerateWordReport($error);
            if ($wordReport && file_exists($wordReport)) {
                $wordFilename = sprintf(
                    'error_report_%d_%s.docx',
                    $error->getId(),
                    $error->getDate()->format('Ymd_His')
                );

                $attachments[] = [
                    'path' => $wordReport,
                    'filename' => $wordFilename,
                    'contentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];
                $generatedFiles[] = $wordReport;
                $context['attachments_generated'] = true;
            }

            $this->send(
                $this->emailSender,
                $this->emailReceiver,
                '🚨 Erreur Critique #' . $error->getId() . ' - ' . $error->getMessage(),
                'emails/critical_error.html.twig',
                $context,
                $attachments
            );
            $this->cleanupGeneratedFiles($generatedFiles);
        } catch (\Exception $e) {
            $this->cleanupGeneratedFiles($generatedFiles);
            error_log('Erreur lors de la notification d\'erreur critique: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanupGeneratedFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file && file_exists($file)) {
                try {
                    unlink($file);
                } catch (\Exception $e) {
                    error_log('Impossible de supprimer le fichier temporaire: ' . $file);
                }
            }
        }
    }
}
