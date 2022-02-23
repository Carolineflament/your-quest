<?php

namespace App\Entity;

use App\Repository\ScanQRRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScanQRRepository::class)
 */
class ScanQR
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $scanAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScanAt(): ?\DateTimeImmutable
    {
        return $this->scanAt;
    }

    public function setScanAt(\DateTimeImmutable $scanAt): self
    {
        $this->scanAt = $scanAt;

        return $this;
    }
}
