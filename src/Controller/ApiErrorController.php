<?php

namespace App\Controller;

use App\Entity\ErrorTicket;
use App\Repository\ErrorTicketRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiErrorController extends AbstractController
{
    #[Route('/api/errors', name: 'api_error_create', methods: ['POST'])]
    public function receiveError(Request $request, ErrorTicketRepository $errorTicketRepository,SendMailService $sendMailService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['hash'], $data['message'], $data['file'], $data['line'], $data['projectName'])) {
            return $this->json(['error' => 'Données invalides ou incomplètes.'], 400);
        }

        $existing = $errorTicketRepository->findOneBy(['hash' => $data['hash']]);

        if ($existing) {
            $existing->setDate(new \DateTime());
            $existing->setCount($existing->getCount() + 1);
            $errorTicketRepository->add($existing, true);
            return $this->json(['message' => 'Erreur existante mise à jour.'], 200);
        }

        $error = new ErrorTicket();
        $error->setDate(new \DateTime($data['date']));
        $error->setMessage($data['message']);
        $error->setTrace($data['trace']);
        $error->setStatusCode($data['status_code']);
        $error->setUrl($data['url']);
        $error->setMethod($data['method']);
        $error->setController($data['controller']);
        $error->setFile($data['file']);
        $error->setLine($data['line']);
        $error->setHash($data['hash']);
        $error->setCount($data['count']);
        $error->setPriority($data['priority']);
        $error->setStatus($data['status']);
        $error->setService($data['service_name'] ?? 'unknown');
        $error->setProjectName($data['projectName']);

        $errorTicketRepository->add($error, true);


            /* if ($data['priority'] === 1) {
                $sendMailService->send(
                    'depps@myonmci.ci',
                    $request->get('email'),
                    'Informations',
                    'content_mail',
                    $context
                );;
            } */

        return $this->json(['message' => 'Erreur enregistrée avec succès.'], 201);
    }
}
