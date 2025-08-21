<?php


namespace App\Controller;

use App\Controller\Config\ApiInterface;
use App\Service\SendMailService;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Sentry\HttpClient\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/statistique')]
#[OA\Tag(name: 'statistique')]
class ApiStatistiqueController extends ApiInterface
{

    #[Route('/send_mail', name: 'api_auth_send_mail', methods: ['POST', "GET"])]
    public function sendMail(Request $request, SendMailService $sendMailService): JsonResponse
    {
        $info_user = [
            'login' => "konatenhamed@gmail.com",
            'password' => "eeeee"
        ];

        $context = compact('info_user');

        $sendMailService->send(
            'konatehamed@kiffelesport.com',
            "konatenhamed@gmail.com",
            'Informations',
            'emails/critical_error.html.twig',
            $context
        );

        return new JsonResponse(['message' => 'Cette route est gérée par LexikJWTAuthenticationBundle'], 200);
    }

    #[Route('/debug-env', name: 'debug_env')]
    public function debugEnv(LoggerInterface $logger): JsonResponse
    {
        $logger->info('MAILER_DSN: ' . $_ENV['MAILER_DSN']);
        $logger->info('EMAIL_SENDER: ' . $_ENV['EMAIL_SENDER']);

        return new JsonResponse([
            'MAILER_DSN' => $_ENV['MAILER_DSN'],
            'EMAIL_SENDER' => $_ENV['EMAIL_SENDER']
        ]);
    }

    #[Route('/test-email-send', name: 'test_email_send')]
public function testEmailSend(MailerInterface $mailer, LoggerInterface $logger): JsonResponse
{
    try {
        $email = (new Email())
            ->from('konatehamed@kiffelesport.com')
            ->to('konatenhamed@gmail.com')
            ->subject('Test SMTP - ' . date('Y-m-d H:i:s'))
            ->text('Ceci est un test d\'envoi SMTP depuis Symfony');

        $mailer->send($email);
        
        $logger->info('Email de test envoyé avec succès');
        
        return new JsonResponse([
            'status' => 'success', 
            'message' => 'Email de test envoyé'
        ]);
        
    } catch (\Exception $e) {
        $logger->error('Erreur envoi email: ' . $e->getMessage());
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
}
}
