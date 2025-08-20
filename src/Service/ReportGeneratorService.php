<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use App\Entity\ErrorTicket;

class ReportGeneratorService
{
    public function generateExcelReport(ErrorTicket $error): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rapport d\'Erreur Critique');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

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

        $row = 3;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item[0]);
            $sheet->setCellValue('B' . $row, $item[1] ?? 'N/A');
            $row++;
        }

        if (method_exists($error, 'getStackTrace') && !empty($error->getStackTrace())) {
            $sheet->setCellValue('A' . ($row + 2), 'Stack Trace');
            $sheet->getStyle('A' . ($row + 2))->getFont()->setBold(true);
            
            $traceRow = $row + 3;
            foreach ((array)$error->getStackTrace() as $trace) {
                $sheet->setCellValue('A' . $traceRow, $trace);
                $traceRow++;
            }
        }

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = tempnam(sys_get_temp_dir(), 'error_report_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return $filename;
    }

    public function generateWordReport(ErrorTicket $error): string
    {
        $phpWord = new PhpWord();
        
        $section = $phpWord->addSection();
        
        $titleStyle = ['bold' => true, 'size' => 16, 'alignment' => Jc::CENTER];
        $headerStyle = ['bold' => true, 'size' => 12];
        $normalStyle = ['size' => 11];

        $section->addText('Rapport d\'Erreur Critique', $titleStyle);
        $section->addTextBreak(2);

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

        if (method_exists($error, 'getStackTrace') && !empty($error->getStackTrace())) {
            $section->addTextBreak(2);
            $section->addText('Stack Trace:', $headerStyle);
            $section->addTextBreak(1);
            
            foreach ((array)$error->getStackTrace() as $index => $trace) {
                $section->addText(($index + 1) . ". " . $trace, ['size' => 9]);
            }
        }

        $filename = tempnam(sys_get_temp_dir(), 'error_report_') . '.docx';
        
        try {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($filename);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la génération du document Word: ' . $e->getMessage());
        }

        return $filename;
    }

    public function safeGenerateExcelReport(ErrorTicket $error): ?string
    {
        try {
            return $this->generateExcelReport($error);
        } catch (\Exception $e) {
            error_log('Erreur génération Excel: ' . $e->getMessage());
            return null;
        }
    }

    public function safeGenerateWordReport(ErrorTicket $error): ?string
    {
        try {
            return $this->generateWordReport($error);
        } catch (\Exception $e) {
            error_log('Erreur génération Word: ' . $e->getMessage());
            return null;
        }
    }
}