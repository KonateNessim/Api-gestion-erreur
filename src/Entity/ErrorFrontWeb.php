<?php

namespace App\Entity;

use App\Repository\ErrorFrontWebRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ErrorFrontWebRepository::class)]
#[Table(name: 'error_front_web')]
class ErrorFrontWeb extends ErrorTicket
{

     #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(["group_1"])]
    private ?string $browser;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(["group_1"])]
    private ?string $browserVersion;

   

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;
        return $this;
    }   

    public function getBrowserVersion(): ?string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(?string $browserVersion): self
    {
        $this->browserVersion = $browserVersion;
        return $this;
    }

}
