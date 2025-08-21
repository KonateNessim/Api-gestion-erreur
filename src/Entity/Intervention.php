<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ "group_1"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups([ "group_1"])]
    private ?\DateTime $dateIntervention = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([ "group_1"])]
    private ?string $message = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([ "group_1"])]
    private ?string $user = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?ErrorTicket $ticket = null;

    #[ORM\Column(length: 255,)]
    #[Groups([ "group_1"])]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateIntervention(): ?\DateTime
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(?\DateTime $dateIntervention): static
    {
        $this->dateIntervention = $dateIntervention;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTicket(): ?ErrorTicket
    {
        return $this->ticket;
    }

    public function setTicket(?ErrorTicket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
