<?php

namespace App\Entity;

use App\Repository\ErrorTicketRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

#[ORM\Entity(repositoryClass: ErrorTicketRepository::class)]
#[Table(name: 'error_log')]
class ErrorTicket
{

    const PRIORITY =[
        "Critique" => 1,
        "Moyen" => 3,
        "Faible" => 4
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'text')]
    private string $trace;

    #[ORM\Column(type: 'integer')]
    private int $statusCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'string', length: 10)]
    private string $method;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $controller;

    #[ORM\Column(type: 'string', length: 255)]
    private string $file;

    #[ORM\Column(type: 'integer')]
    private int $line;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $hash;

    #[ORM\Column(type: 'integer')]
    private int $count = 1;

    #[ORM\Column(type: 'smallint')]
    private int $priority;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'new';

    #[ORM\Column(type: 'string', length: 100)]
    private string $service;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $projectName = null;



    public function getId(): ?int { return $this->id; }

    public function getDate(): \DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): void { $this->date = $date; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): void { $this->message = $message; }

    public function getTrace(): string { return $this->trace; }
    public function setTrace(string $trace): void { $this->trace = $trace; }

    public function getStatusCode(): int { return $this->statusCode; }
    public function setStatusCode(int $statusCode): void { $this->statusCode = $statusCode; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): void { $this->url = $url; }

    public function getMethod(): string { return $this->method; }
    public function setMethod(string $method): void { $this->method = $method; }

    public function getController(): ?string { return $this->controller; }
    public function setController(?string $controller): void { $this->controller = $controller; }

    public function getFile(): string { return $this->file; }
    public function setFile(string $file): void { $this->file = $file; }

    public function getLine(): int { return $this->line; }
    public function setLine(int $line): void { $this->line = $line; }

    public function getHash(): string { return $this->hash; }
    public function setHash(string $hash): void { $this->hash = $hash; }

    public function getCount(): int { return $this->count; }
    public function setCount(int $count): void { $this->count = $count; }
    public function incrementCount(): void { $this->count++; }

    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): void { $this->priority = $priority; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getService(): string { return $this->service; }
    public function setService(string $service): void { $this->service = $service; }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }
}


