<?php

namespace Linotype\Bundle\LinotypeBundle\Entity;

use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinotypeMetaRepository::class)
 */
class LinotypeMeta
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
    private $context_key;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $context_value;

    /**
     * @ORM\Column(type="integer")
     */
    private $template_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContextKey(): ?string
    {
        return $this->context_key;
    }

    public function setContextKey(string $context_key): self
    {
        $this->context_key = $context_key;

        return $this;
    }

    public function getContextValue(): ?string
    {
        return $this->context_value;
    }

    public function setContextValue(string $context_value): self
    {
        $this->context_value = $context_value;

        return $this;
    }

    public function getTemplateId(): ?int
    {
        return $this->template_id;
    }

    public function setTemplateId(int $template_id): self
    {
        $this->template_id = $template_id;

        return $this;
    }
}
