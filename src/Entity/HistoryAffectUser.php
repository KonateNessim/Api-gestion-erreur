<?php

namespace App\Entity;

use App\Repository\HistoryAffectUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HistoryAffectUserRepository::class)]
class HistoryAffectUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["group_1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["group_1"])]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'historyAffectUsers')]
    private ?ErrorTicket $ticket = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["group_1"])]
    private ?\DateTime $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

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

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }
}
