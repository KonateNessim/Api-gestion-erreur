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
    private ReportGeneratorService $reportGenerator;

    public function __construct(
        MailerInterface $mailer,
        ReportGeneratorService $reportGenerator,
        string $emailSender,
        string $emailReceiver
    ) {
        $this->mailer = $mailer;
        $this->reportGenerator = $reportGenerator;
        $this->emailSender = $emailSender;
        $this->emailReceiver = $emailReceiver;
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
        
        // Nettoyage des fichiers après envoi
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

            // Génération du rapport Excel
            $excelReport = $this->reportGenerator->safeGenerateExcelReport($error);
            if ($excelReport && file_exists($excelReport)) {
                $excelFilename = sprintf(
                    'error_report_%d_%s.xlsx',
                    $error->getId(),
                    $error->getDate()->format('Ymd_His')
                );

                $attachments[] = [
                    'path' => $excelReport,
                    'filename' => $excelFilename
                ];
                $generatedFiles[] = $excelReport;
                $context['attachments_generated'] = true;
            }

            // Génération du rapport Word
            $wordReport = $this->reportGenerator->safeGenerateWordReport($error);
            if ($wordReport && file_exists($wordReport)) {
                $wordFilename = sprintf(
                    'error_report_%d_%s.docx',
                    $error->getId(),
                    $error->getDate()->format('Ymd_His')
                );

                $attachments[] = [
                    'path' => $wordReport,
                    'filename' => $wordFilename
                ];
                $generatedFiles[] = $wordReport;
                $context['attachments_generated'] = true;
            }

            // Envoi de l'email
            $this->send(
                $this->emailSender,
                $this->emailReceiver,
                '🚨 Erreur Critique #' . $error->getId() . ' - ' . substr($error->getMessage(), 0, 50) . '...',
                'emails/critical_error.html.twig',
                $context,
                $attachments
            );

            // Nettoyage supplémentaire (redondant mais sécurisé)
            $this->cleanupGeneratedFiles($generatedFiles);

        } catch (\Exception $e) {
            // Nettoyage en cas d'erreur
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

    /**
     * Méthode utilitaire pour envoyer des emails simples sans attachments
     */
    public function sendSimpleEmail(
        string $subject,
        string $template,
        array $context = [],
        ?string $to = null,
        ?string $from = null
    ): void {
        $this->send(
            $from ?? $this->emailSender,
            $to ?? $this->emailReceiver,
            $subject,
            $template,
            $context
        );
    }

    /**
     * Méthode pour envoyer des emails avec des pièces jointes personnalisées
     */
    public function sendWithAttachments(
        string $subject,
        string $template,
        array $attachments = [],
        array $context = [],
        ?string $to = null,
        ?string $from = null
    ): void {
        $this->send(
            $from ?? $this->emailSender,
            $to ?? $this->emailReceiver,
            $subject,
            $template,
            $context,
            $attachments
        );
    }
}
