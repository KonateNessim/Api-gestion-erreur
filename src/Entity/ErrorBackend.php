<?php

namespace App\Entity;

use App\Repository\ErrorBackendRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ErrorBackendRepository::class)]
#[Table(name: 'error_backend')]
class ErrorBackend extends ErrorTicket
{

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["group_1"])]
    private ?string $controller;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["group_1"])]
    private string $file;

    public function getController(): ?string
    {
        return $this->controller;
    }
    public function setController(?string $controller): void
    {
        $this->controller = $controller;
    }


    public function getFile(): string
    {
        return $this->file;
    }
    public function setFile(string $file): void
    {
        $this->file = $file;
    }
}
