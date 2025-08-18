<?php

namespace App\EventListener;

use App\Entity\ErrorTicket;
use App\Service\SendMailService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private SendMailService $mailer
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $controller = $request->attributes->get('_controller');
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $hash = hash('sha256', $message . $file . $line);

        try {
            $existing = $this->em->getRepository(ErrorTicket::class)->findOneBy(['hash' => $hash]);

            if ($existing) {
                $existing->setDate(new \DateTime());
                $existing->incrementCount();
                $this->em->flush();
                return;
            }

            $priority = $this->determinePriority($exception, $statusCode);

            $errorLog = new ErrorTicket();
            $errorLog->setDate(new \DateTime());
            $errorLog->setMessage($message);
            $errorLog->setTrace($exception->getTraceAsString());
            $errorLog->setStatusCode($statusCode);
            $errorLog->setUrl($request->getUri());
            $errorLog->setMethod($request->getMethod());
            $errorLog->setController($controller);
            $errorLog->setFile($file);
            $errorLog->setLine($line);
            $errorLog->setHash($hash);
            $errorLog->setCount(1);
            $errorLog->setPriority($priority);
            $errorLog->setStatus('new');
            $errorLog->setProjectName('PorjetGestionErreur');

            $this->em->persist($errorLog);
            $this->em->flush();

            $this->logger->error('Exception capturée dans le listener', [
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'controller' => $controller,
                'priority' => $priority,
            ]);
        } catch (\Throwable $e) {
            $this->logger->critical('Erreur dans ExceptionListener: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function determinePriority(\Throwable $exception, int $statusCode): int
    {
       
        if ($exception instanceof UniqueConstraintViolationException) {
            return 4;
        }
        return match (true) {
            $statusCode >= 500 => ErrorTicket::PRIORITY["Critique"], 
            $statusCode >= 400 => ErrorTicket::PRIORITY["Moyen"], 
            default => ErrorTicket::PRIORITY["Faible"],            
        };
    }
}
