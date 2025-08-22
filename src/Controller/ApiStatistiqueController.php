<?php


namespace App\Controller;

use App\Controller\Config\ApiInterface;
use App\Entity\ErrorTicket;
use App\Repository\ErrorTicketRepository;
use App\Service\ReportGeneratorService;
use App\Service\SendMailService;
use Error;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/statistique')]
#[OA\Tag(name: 'statistique')]
class ApiStatistiqueController extends ApiInterface
{

    #[Route('/',methods: ['POST', "GET"])]
    /**
     * statistiques.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ErrorTicket::class, groups: ['full']))
        )
    )]
    public function index( ErrorTicketRepository $errorTicketRepository): JsonResponse
    {
    

        $errorCritiqueBackend = $errorTicketRepository->findBy(['type' => 'backend','priority'=> 1]);
        $errorCritiqueMobile = $errorTicketRepository->findBy(['type' => 'frontendMobile','priority'=> 1]);
        $errorCritiqueWeb = $errorTicketRepository->findBy(['type' => 'frontendWeb','priority'=> 1]);

        $allErrors = [
            'backend' => count($errorCritiqueBackend),
            'frontendMobile' => count($errorCritiqueMobile),
            'frontendWeb' => count($errorCritiqueWeb)
        ];

    
        $response =  $this->responseData($allErrors, 'group_1', ['Content-Type' => 'application/json']);
        return $response;
    }
}
