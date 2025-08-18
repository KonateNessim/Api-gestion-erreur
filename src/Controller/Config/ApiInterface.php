<?php

namespace App\Controller\Config;

use App\Controller\FileTrait;    
use App\Repository\UserRepository;
use App\Service\Menu;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiInterface extends AbstractController
{
    use FileTrait;

    protected const UPLOAD_PATH = 'media_deeps';
    protected $security;
    protected $validator;
    protected $userInterface;
    protected  $hasher;
    protected  $userRepository;
    protected  $utils;
    //protected  $utils;
    protected $em;

    protected $client;

    protected $serializer;

    public function __construct(EntityManagerInterface $em, Utils $utils,UserPasswordHasherInterface $hasher, HttpClientInterface $client, SerializerInterface $serializer, ValidatorInterface $validator, UserRepository $userRepository)
    {

        $this->client = $client;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->utils = $utils;
        $this->hasher = $hasher;
    }


    /**
     * @var integer HTTP status code - 200 (OK) by default
     */
    protected $statusCode = 200;
    protected $message = "Operation effectuée avec succes";

    /**
     * Gets the value of statusCode.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param integer $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
    protected function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function response($data, $headers = [])
    {
        $encoders = [new JsonEncoder()];

        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);


        if ($data == null) {
            $arrayData = [
                'data' => '[]',
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ];
            $response = $this->json([
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode(),
                'errors' => []

            ], 200);
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $arrayData = [
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ];
            $jsonContent = $serializer->serialize($arrayData, 'json', [
                'circular_reference_handler' => function ($object) {
                    return  $object->getId();
                },

            ]);
        
            $response = new Response($jsonContent);
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
  

        return $response;
       
    }
   
    public function responseAdd($data, $headers = [])
    {
        return $this->json([
            'data' => $data,
            'message' => $this->getMessage(),
            'status' => $this->getStatusCode()

        ], 200);
    }

    public function responseData($data = [], $group = null, $headers = [])
    {
        try {

            $finalHeaders = empty($headers) ? ['Content-Type' => 'application/json'] : $headers;
            if ($data) {
                $context = [AbstractNormalizer::GROUPS => $group];
                $json = $this->serializer->serialize($data, 'json', $context);
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => json_decode($json),
                    'errors' => []
                ], 200, $finalHeaders);
                  $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => [],
                    'errors' => []
                ], 200, $finalHeaders);
                  $response->headers->set('Access-Control-Allow-Origin', '*');
            }
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => []
            ], 500, $finalHeaders);
        }

        return $response;
    }

    public function errorResponse($DTO,string $customMessage = ''): ?JsonResponse
    {
        $errors = $this->validator->validate($DTO);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];
            
            return new JsonResponse($response, 400);
        }elseif ($customMessage != '') {
            $errorMessages[] = $customMessage;
            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

            return new JsonResponse($response, 400);
        }

        return null;
    }

}
