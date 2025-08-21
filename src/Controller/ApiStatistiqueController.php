<?php


namespace App\Controller;

use App\Controller\Config\ApiInterface;
use App\Repository\ErrorTicketRepository;
use App\Service\ReportGeneratorService;
use App\Service\SendMailService;
use Error;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Sentry\HttpClient\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/statistique')]
#[OA\Tag(name: 'statistique')]
class ApiStatistiqueController extends ApiInterface
{

    #[Route('/send_mail', name: 'api_auth_send_mail', methods: ['POST', "GET"])]
    public function generateReport(ReportGeneratorService $reportService, ErrorTicketRepository $errorTicketRepository)
    {
        $error = $errorTicketRepository->find(17);


        if (!$error) {
            return new JsonResponse(['error' => 'Erreur non trouvée'], 404);
        }

        try {
            $excelFile = $reportService->generateExcelReport($error);

            return new BinaryFileResponse($excelFile, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="error_report.xlsx"'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
