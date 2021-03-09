<?php

namespace Linotype\SymfonyBundle\Entity;

use Linotype\SymfonyBundle\Repository\LinotypeOptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinotypeOptionRepository::class)
 */
class LinotypeOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $option_key;

    /**
     * @ORM\Column(type="text")
     */
    private $option_value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOptionKey(): ?string
    {
        return $this->option_key;
    }

    public function setOptionKey(string $option_key): self
    {
        $this->option_key = $option_key;

        return $this;
    }

    public function getOptionValue(): ?string
    {
        return $this->option_value;
    }

    public function setOptionValue(string $option_value): self
    {
        $this->option_value = $option_value;

        return $this;
    }
}
