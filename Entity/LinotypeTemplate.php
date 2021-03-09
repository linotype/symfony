<?php

namespace Linotype\Bundle\Entity;

use Linotype\Bundle\Repository\LinotypeTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinotypeTemplateRepository::class)
 */
class LinotypeTemplate
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
    private $template_key;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $template_type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplateKey(): ?string
    {
        return $this->template_key;
    }

    public function setTemplateKey(string $template_key): self
    {
        $this->template_key = $template_key;

        return $this;
    }

    public function getTemplateType(): ?string
    {
        return $this->template_type;
    }

    public function setTemplateType(string $template_type): self
    {
        $this->template_type = $template_type;

        return $this;
    }
}
