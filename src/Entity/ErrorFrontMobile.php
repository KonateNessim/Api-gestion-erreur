<?php

namespace App\Entity;

use App\Repository\ErrorFrontMobileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ErrorFrontMobileRepository::class)]
class ErrorFrontMobile extends ErrorTicket
{

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $osVersion;


    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $appVersion = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private string $platform;


    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;
        return $this;
    }

    public function getAppVersion(): ?string
    {
        return $this->appVersion;
    }

    public function setAppVersion(?string $appVersion): self
    {
        $this->appVersion = $appVersion;
        return $this;
    }


    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = $osVersion;
        return $this;
    }

   
}
