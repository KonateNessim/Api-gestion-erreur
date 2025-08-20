<?php

namespace App\Controller;

use App\Controller\Config\ApiInterface;
use App\Entity\ErrorBackend;
use App\Entity\ErrorFrontMobile;
use App\Entity\ErrorFrontWeb;
use App\Entity\ErrorTicket;
use App\Entity\Intervention;
use App\Repository\ErrorTicketRepository;
use App\Repository\InterventionRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Paginator;
use App\Service\ReportGeneratorService;
use DateTime;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;


#[Route('/api')]
#[OA\Tag(name: 'Errors')]
class ApiErrorController extends ApiInterface
{


    #[Route('/errors', name: 'api_error_create', methods: ['POST'])]
    /**
     * Permet de créer une erreur.
     */
    #[OA\Post(
        summary: "Crée une erreur",
        description: "Crée une erreur.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    new OA\Property(property: "priority", type: "string"),
                    new OA\Property(property: "hash", type: "string"),
                    new OA\Property(property: "url", type: "string"),
                    new OA\Property(property: "status", type: "string"),
                    new OA\Property(property: "browser", type: "string"),
                    new OA\Property(property: "line", type: "string"),
                    new OA\Property(property: "method", type: "string"),
                    new OA\Property(property: "projectName", type: "string"),
                    new OA\Property(property: "statusCode", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),
                    new OA\Property(property: "stackTrace", type: "string"),
                    new OA\Property(property: "browserVersion", type: "string"),
                    new OA\Property(property: "appVersion", type: "string"),
                    new OA\Property(property: "platform", type: "string"),
                    new OA\Property(property: "osVersion", type: "string"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function receiveError(
        Request $request,
        ErrorTicketRepository $errorTicketRepository,
        EntityManagerInterface $em,
        SendMailService $sendMailService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['type'], $data['hash'], $data['message'])) {
            return $this->json(['error' => 'Données invalides ou incomplètes.'], 400);
        }

        $existingError = $errorTicketRepository->findOneBy(['hash' => $data['hash']]);

        if ($existingError && $existingError->getStatus() !== 'resolved') {
            $existingError->incrementCount();
            $em->flush();
            return $this->json(['message' => 'Erreur existante mise à jour.'], 200);
        }

        try {
            switch ($data['type']) {
                case 'backend':
                    $error = $this->createBackendError($data);
                    break;

                case 'frontendWeb':
                    $error = $this->createFrontendErrorWeb($data);
                    break;

                case 'frontendMobile':
                    $error = $this->createFrontendErrorMobile($data);
                    break;

                default:
                    return $this->json(['error' => 'Type d\'erreur non supporté'], 400);
            }

            $em->persist($error);
            $em->flush();

            if ($error->getPriority() == 1) {
                $sendMailService->handleCriticalErrorNotification($error);
            }

            return $this->json(['message' => 'Erreur enregistrée avec succès.'], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du traitement: ' . $e->getMessage()], 500);
        }
    }

    private function createBackendError(array $data): ErrorBackend
    {
        $requiredFields = ['file', 'line', 'trace', 'status_code'];
        $this->validateRequiredFields($data, $requiredFields);

        $error = new ErrorBackend();
        $error->setDate(new \DateTime($data['date'] ?? 'now'));
        $error->setMessage($data['message']);
        $error->setStatusCode($data['status_code']);
        $error->setUrl($data['url'] ?? null);
        $error->setMethod($data['method'] ?? null);
        $error->setController($data['controller'] ?? null);
        $error->setFile($data['file']);
        $error->setLine($data['line']);
        $error->setHash($data['hash']);
        $error->setCount($data['count'] ?? 1);
        $error->setType($data['type']);
        $error->setPriority($data['priority'] ?? 0);
        $error->setStatus($data['status'] ?? 'new');
        $error->setStackTrace($data['trace']);
        $error->setProjectName($data['projectName'] ?? null);

        return $error;
    }

    private function createFrontendErrorWeb(array $data): ErrorFrontWeb
    {
        $requiredFields = ['platform', 'url',];
        $this->validateRequiredFields($data, $requiredFields);

        $error = new ErrorFrontWeb();
        $error->setDate(new \DateTime($data['date'] ?? 'now'));
        $error->setMessage($data['message']);
        $error->setHash($data['hash']);
        $error->setLine($data['line']);

        $error->setMethod($data['method'] ?? null);
        $error->setUrl($data['url']);
        $error->setType($data['type']);
        $error->setStackTrace($data['trace']);
        $error->setStatusCode($data['status_code']);
        $error->setCount($data['count'] ?? 1);
        $error->setBrowser($data['browser'] ?? null);
        $error->setBrowserVersion($data['browser_version'] ?? null);
        $error->setStackTrace($data['stack_trace'] ?? []);
        $error->setProjectName($data['projectName'] ?? null);
        $error->setPriority($data['priority'] ?? 0);
        $error->setStatus($data['status'] ?? 'new');

        return $error;
    }
    private function createFrontendErrorMobile(array $data): ErrorFrontMobile
    {
        $requiredFields = ['platform', 'url', 'environment'];
        $this->validateRequiredFields($data, $requiredFields);

        $error = new ErrorFrontMobile();
        $error->setDate(new \DateTime($data['date'] ?? 'now'));
        $error->setMessage($data['message']);
        $error->setHash($data['hash']);
        $error->setLine($data['line']);
        $error->setUrl($data['url']);
        $error->setStatusCode($data['status_code']);
        $error->setMethod($data['method'] ?? null);
        $error->setType($data['type']);
        $error->setOsVersion($data['os_version']);
        $error->setCount($data['count'] ?? 1);
        $error->setPlatform($data['platform']);
        $error->setAppVersion($data['app_version'] ?? null);
        $error->setStackTrace($data['stack_trace'] ?? []);
        $error->setAppVersion($data['app_version'] ?? null);
        $error->setProjectName($data['projectName'] ?? null);
        $error->setPriority($data['priority'] ?? 0);
        $error->setStatus($data['status'] ?? 'new');

        return $error;
    }

    private function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Le champ $field est requis");
            }
        }
    }


    #[Route('/liste', name: 'list', methods: ['GET'])]
    /**
     * Retourne la liste des erreurs.
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
    public function listErrors(Request $request, ErrorTicketRepository $errorTicketRepository, Paginator $paginator): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $allErrors = $errorTicketRepository->findAll();

        $paginationResult = $paginator->paginate($allErrors, $page, $limit);

        return $this->json($paginationResult->toArray());
    }



    #[Route('/{id}/status', name: 'api_error_ticket_update_status', methods: ['POST', 'PUT'])]
    #[OA\Post(
        summary: "Modifier le statut de l'erreur",
        description: "Modifier le statut de l'erreur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user", type: "string"),
                    new OA\Property(property: "status", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function updateStatus(
        ErrorTicket $errorTicket,
        Request $request,
        ErrorTicketRepository $repository
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        try {
            $errorTicket->setStatus($data['status']);
            $errorTicket->setUserUpdate($data['user']);
            $errorTicket->setUpdateDate(new DateTime());

            $errors = $this->errorResponse($errorTicket);
            if ($errors !== null) {
                return $errors;
            }

            $repository->add($errorTicket, true);

            return $this->responseData($errorTicket, 'group_1', ['Content-Type' => 'application/json']);
        } catch (\InvalidArgumentException $e) {
            $this->setMessage($e->getMessage());
            $response = $this->response('[]');
            return $response;
        }
    }

    #[Route('/interventions/{id}', name: 'api_bulk_intervention_upsert', methods: ['POST'])]
    #[OA\Post(
        path: '/api/interventions/{id}',
        summary: 'Gestion des interventions',
        description: 'Crée ou met à jour des interventions pour une erreur spécifique',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "interventions",
                        type: "array",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", type: "string"),
                                new OA\Property(property: "message", type: "string"),
                                new OA\Property(property: "dateIntervention", type: "string", format: "date-time"),
                            ]
                        )
                    ),
                ]
            )
        )
    )]
    public function intervention(Request $request, ErrorTicket $errorTicket,  InterventionRepository $interventionRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation de base
        if (!isset($data['interventions']) || !is_array($data['interventions'])) {
            return $this->json(['error' => 'Le champ interventions doit être un tableau'], 400);
        }


        foreach ($data['interventions'] as $interventionData) {
            $this->processIntervention($interventionData, $errorTicket, $interventionRepo);
        }

        $this->em->flush();

        return $this->responseData($errorTicket, 'group_1', ['Content-Type' => 'application/json']);
    }

    private function processIntervention(array $data, ErrorTicket $errorTicket, InterventionRepository $interventionRepo): array
    {
        $result = [
            'input' => $data,
            'success' => false,
            'action' => null
        ];

        try {


            if (isset($data['id']) != null) {
                $intervention = $interventionRepo->find($data['id']);
                if (!$intervention) {
                    throw new \InvalidArgumentException("Intervention non trouvée");
                }
            } else {
                $intervention = new Intervention();
            }

            $intervention->setMessage($data['message']);
            $intervention->setDateIntervention(new \DateTime($data['dateIntervention']));
            /* $intervention->setUser($data['user']); */
            $intervention->setErrorTicket($errorTicket);

            $errors = $this->validator->validate($intervention);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException((string) $errors);
            }


            $intervention->add($intervention, true);

            $result['success'] = true;
            $result['intervention'] = [
                'id' => $intervention->getId(),
                'message' => $intervention->getMessage(),
                'dateIntervention' => $intervention->getDateIntervention()->format('c'),
                'user' => $intervention->getUser(),
                'errorTicketId' => $intervention->getErrorTicket()->getId()
            ];
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
