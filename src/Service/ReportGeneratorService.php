<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use App\Entity\ErrorTicket;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ReportGeneratorService
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    private function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            $this->logger->$level($message);
        } else {
            error_log('[' . strtoupper($level) . '] ' . $message);
        }
    }

    public function generateExcelReport(ErrorTicket $error): string
    {
        $this->log("Début génération Excel pour l'erreur ID: " . $error->getId());

        try {
            // Vérification du répertoire temporaire
            $tempDir = sys_get_temp_dir();
            $this->log("Répertoire temporaire: " . $tempDir);
            
            if (!is_writable($tempDir)) {
                throw new RuntimeException("Le répertoire temporaire n'est pas accessible en écriture: " . $tempDir);
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Titre principal
            $sheet->setCellValue('A1', 'Rapport d\'Erreur Critique');
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Données de base
            $data = [
                ['ID', $error->getId()],
                ['Date', $error->getDate()->format('Y-m-d H:i:s')],
                ['Message', $error->getMessage()],
                ['Type', $error->getType()],
                ['Priorité', $error->getPriority()],
                ['Statut', $error->getStatus()],
                ['URL', $error->getUrl() ?? 'N/A'],
                ['Projet', $error->getProjectName() ?? 'N/A'],
                ['Nombre d\'occurrences', $error->getCount()],
            ];

            // Données spécifiques selon le type d'erreur
            if ($error instanceof \App\Entity\ErrorBackend) {
                $data = array_merge($data, [
                    ['Fichier', $error->getFile() ?? 'N/A'],
                    ['Ligne', $error->getLine() ?? 'N/A'],
                    ['Status Code', $error->getStatusCode() ?? 'N/A'],
                    ['Méthode', $error->getMethod() ?? 'N/A'],
                    ['Controller', $error->getController() ?? 'N/A'],
                ]);
            } elseif ($error instanceof \App\Entity\ErrorFrontWeb) {
                $data = array_merge($data, [
                    ['Navigateur', $error->getBrowser() ?? 'N/A'],
                    ['Version navigateur', $error->getBrowserVersion() ?? 'N/A'],
                ]);
            } elseif ($error instanceof \App\Entity\ErrorFrontMobile) {
                $data = array_merge($data, [
                    ['Plateforme', $error->getPlatform() ?? 'N/A'],
                    ['Version OS', $error->getOsVersion() ?? 'N/A'],
                    ['Version App', $error->getAppVersion() ?? 'N/A'],
                ]);
            }

            // Remplissage des données
            $row = 3;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item[0]);
                $sheet->setCellValue('B' . $row, $item[1] ?? 'N/A');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
            }

            // Stack Trace si disponible
            if (method_exists($error, 'getStackTrace') && !empty($error->getStackTrace())) {
                $sheet->setCellValue('A' . ($row + 2), 'Stack Trace');
                $sheet->getStyle('A' . ($row + 2))->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
                
                $traceRow = $row + 3;
                $stackTrace = is_array($error->getStackTrace()) ? $error->getStackTrace() : [$error->getStackTrace()];
                
                foreach ($stackTrace as $trace) {
                    if (is_string($trace)) {
                        $sheet->setCellValue('A' . $traceRow, $trace);
                        $sheet->getStyle('A' . $traceRow)->getAlignment()->setWrapText(true);
                        $traceRow++;
                    }
                }
            }

            // Ajustement automatique des colonnes
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Génération du fichier
            $filename = $tempDir . '/error_report_' . uniqid() . '.xlsx';
            $this->log("Tentative de création du fichier: " . $filename);

            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);

            if (!file_exists($filename)) {
                throw new RuntimeException("Le fichier Excel n'a pas été créé: " . $filename);
            }

            $this->log("Fichier Excel généré avec succès: " . $filename . " (" . filesize($filename) . " bytes)");
            return $filename;

        } catch (\Exception $e) {
            $this->log("ERREUR lors de la génération Excel: " . $e->getMessage(), 'error');
            $this->log("Trace: " . $e->getTraceAsString(), 'error');
            throw new RuntimeException("Erreur lors de la génération du rapport Excel: " . $e->getMessage(), 0, $e);
        }
    }

    public function generateWordReport(ErrorTicket $error): string
    {
        $this->log("Début génération Word pour l'erreur ID: " . $error->getId());

        try {
            // Vérification du répertoire temporaire
            $tempDir = sys_get_temp_dir();
            $this->log("Répertoire temporaire: " . $tempDir);
            
            if (!is_writable($tempDir)) {
                throw new RuntimeException("Le répertoire temporaire n'est pas accessible en écriture: " . $tempDir);
            }

            $phpWord = new PhpWord();
            
            // Styles
            $titleStyle = ['bold' => true, 'size' => 16, 'alignment' => Jc::CENTER];
            $headerStyle = ['bold' => true, 'size' => 12];
            $normalStyle = ['size' => 11];
            $traceStyle = ['size' => 9];

            $section = $phpWord->addSection();
            
            // Titre
            $section->addText('Rapport d\'Erreur Critique', $titleStyle);
            $section->addTextBreak(2);

            // Informations générales
            $section->addText('Informations Générales:', $headerStyle);
            $section->addTextBreak(1);

            $basicInfo = [
                'ID' => $error->getId(),
                'Date' => $error->getDate()->format('Y-m-d H:i:s'),
                'Message' => $error->getMessage(),
                'Type' => $error->getType(),
                'Priorité' => $error->getPriority(),
                'Statut' => $error->getStatus(),
                'URL' => $error->getUrl() ?? 'N/A',
                'Projet' => $error->getProjectName() ?? 'N/A',
                'Occurrences' => $error->getCount(),
            ];

            foreach ($basicInfo as $label => $value) {
                $section->addText("{$label}: {$value}", $normalStyle);
            }

            $section->addTextBreak(2);

            // Détails spécifiques
            $section->addText('Détails Spécifiques:', $headerStyle);
            $section->addTextBreak(1);

            if ($error instanceof \App\Entity\ErrorBackend) {
                $specificInfo = [
                    'Fichier' => $error->getFile() ?? 'N/A',
                    'Ligne' => $error->getLine() ?? 'N/A',
                    'Status Code' => $error->getStatusCode() ?? 'N/A',
                    'Méthode' => $error->getMethod() ?? 'N/A',
                    'Controller' => $error->getController() ?? 'N/A',
                ];
            } elseif ($error instanceof \App\Entity\ErrorFrontWeb) {
                $specificInfo = [
                    'Navigateur' => $error->getBrowser() ?? 'N/A',
                    'Version navigateur' => $error->getBrowserVersion() ?? 'N/A',
                ];
            } elseif ($error instanceof \App\Entity\ErrorFrontMobile) {
                $specificInfo = [
                    'Plateforme' => $error->getPlatform() ?? 'N/A',
                    'Version OS' => $error->getOsVersion() ?? 'N/A',
                    'Version App' => $error->getAppVersion() ?? 'N/A',
                ];
            } else {
                $specificInfo = ['Aucun détail spécifique' => 'N/A'];
            }

            foreach ($specificInfo as $label => $value) {
                $section->addText("{$label}: {$value}", $normalStyle);
            }

            // Stack Trace si disponible
            if (method_exists($error, 'getStackTrace') && !empty($error->getStackTrace())) {
                $section->addTextBreak(2);
                $section->addText('Stack Trace:', $headerStyle);
                $section->addTextBreak(1);
                
                $stackTrace = is_array($error->getStackTrace()) ? $error->getStackTrace() : [$error->getStackTrace()];
                
                foreach ($stackTrace as $index => $trace) {
                    if (is_string($trace)) {
                        $section->addText(($index + 1) . ". " . $trace, $traceStyle);
                    }
                }
            }

            // Génération du fichier
            $filename = $tempDir . '/error_report_' . uniqid() . '.docx';
            $this->log("Tentative de création du fichier Word: " . $filename);

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($filename);

            if (!file_exists($filename)) {
                throw new RuntimeException("Le fichier Word n'a pas été créé: " . $filename);
            }

            $this->log("Fichier Word généré avec succès: " . $filename . " (" . filesize($filename) . " bytes)");
            return $filename;

        } catch (\Exception $e) {
            $this->log("ERREUR lors de la génération Word: " . $e->getMessage(), 'error');
            $this->log("Trace: " . $e->getTraceAsString(), 'error');
            throw new RuntimeException("Erreur lors de la génération du rapport Word: " . $e->getMessage(), 0, $e);
        }
    }

    public function safeGenerateExcelReport(ErrorTicket $error): ?string
    {
        try {
            return $this->generateExcelReport($error);
        } catch (\Exception $e) {
            $this->log('Erreur génération Excel (safe): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    public function safeGenerateWordReport(ErrorTicket $error): ?string
    {
        try {
            return $this->generateWordReport($error);
        } catch (\Exception $e) {
            $this->log('Erreur génération Word (safe): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Nettoie les fichiers temporaires après envoi
     */
    public function cleanupFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            $result = unlink($filePath);
            $this->log("Fichier nettoyé: " . $filePath . " (" . ($result ? 'succès' : 'échec') . ")");
            return $result;
        }
        return true;
    }
}