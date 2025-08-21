<?php

namespace App\Entity;

use App\Repository\ErrorTicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ErrorTicketRepository::class)]
#[Table(name: 'error_log')]
#[InheritanceType("JOINED")]
#[DiscriminatorColumn(name: "discr", type: "string", length: 18)]
#[DiscriminatorMap([
    'errorTicket' => ErrorTicket::class,
    'errorBackend' => ErrorBackend::class,
    'errorFrontWeb' => ErrorFrontWeb::class,
    'errorFrontMobile' => ErrorFrontMobile::class,
])]
class ErrorTicket
{

    const  ErrorStatus = [

        "NEW" => 'new',
        "IN_PROGRESS" => 'in_progress',
        "RESOLVED" > 'resolved'

    ];

    const PRIORITY = [
        "Critique" => 1,
        "Moyen" => 3,
        "Faible" => 4
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["group_1"])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(["group_1"])]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'text')]
    #[Groups(["group_1"])]
    private string $message;

    #[ORM\Column(type: 'integer')]
    #[Groups(["group_1"])]
    private int $statusCode;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["group_1"])]
    private string $url;

    #[ORM\Column(type: 'string', length: 10)]
    #[Groups(["group_1"])]
    private string $method;


    #[ORM\Column(type: 'integer')]
    #[Groups(["group_1"])]
    private int $line;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    #[Groups(["group_1"])]
    private string $hash;

    #[ORM\Column(type: 'integer',nullable: true)]
    #[Groups(["group_1"])]
    private int $count = 1;

    #[ORM\Column(type: 'smallint')]
    #[Groups(["group_1"])]
    private int $priority;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(["group_1"])]
    private string $status = 'new';



    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["group_1"])]
    private ?string $projectName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["group_1"])]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updateDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userUpdate = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(
        targetEntity: Intervention::class,
        mappedBy: 'ticket',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Groups(["group_1"])]
    private Collection $interventions;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(["group_1"])]
    private array $stackTrace = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["group_1"])]
    private ?string $user = null;

    /**
     * @var Collection<int, HistoryAffectUser>
     */
    #[ORM\OneToMany(
        targetEntity: HistoryAffectUser::class,
        mappedBy: 'ticket',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Groups(["group_1"])]
    private Collection $historyAffectUsers;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->historyAffectUsers = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }


    public function getLine(): int
    {
        return $this->line;
    }
    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getCount(): int
    {
        return $this->count;
    }
    public function setCount(int $count): void
    {
        $this->count = $count;
    }
    public function incrementCount(): void
    {
        $this->count++;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }



    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(?\DateTime $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getUserUpdate(): ?string
    {
        return $this->userUpdate;
    }

    public function setUserUpdate(string $userUpdate): static
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setTicket($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getTicket() === $this) {
                $intervention->setTicket(null);
            }
        }

        return $this;
    }


    public function getStackTrace(): array
    {
        return $this->stackTrace;
    }

    public function setStackTrace(array $stackTrace): self
    {
        $this->stackTrace = $stackTrace;
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

    /**
     * @return Collection<int, HistoryAffectUser>
     */
    public function getHistoryAffectUsers(): Collection
    {
        return $this->historyAffectUsers;
    }

    public function addHistoryAffectUser(HistoryAffectUser $historyAffectUser): static
    {
        if (!$this->historyAffectUsers->contains($historyAffectUser)) {
            $this->historyAffectUsers->add($historyAffectUser);
            $historyAffectUser->setTicket($this);
        }

        return $this;
    }

    public function removeHistoryAffectUser(HistoryAffectUser $historyAffectUser): static
    {
        if ($this->historyAffectUsers->removeElement($historyAffectUser)) {
            // set the owning side to null (unless already changed)
            if ($historyAffectUser->getTicket() === $this) {
                $historyAffectUser->setTicket(null);
            }
        }

        return $this;
    }
}
