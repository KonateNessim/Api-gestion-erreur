<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer,private EntityManagerInterface $em)
    {
        $this->mailer = $mailer;
    }

    public function send(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $context
    ): void {
        //On crée le mail
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

        // On envoie le mail
        $this->mailer->send($email);
    }



    /* public function  sendNotification($message, $user, $userUpdate){

        $notification = new Notification();
        $notification->setLibelle($message);
        $notification->setUser($user);
        $notification->setUpdatedBy($userUpdate);
        $notification->setCreatedBy($userUpdate);
        $notification->setUpdatedAt(new \DateTime());
        $notification->setCreatedAtValue(new \DateTime());
        $this->em->persist($notification);
        $this->em->flush();

    } */
}
