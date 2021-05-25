<?php

namespace Linotype\Bundle\LinotypeBundle\Entity;

use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTranslateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinotypeTranslateRepository::class)
 */
class LinotypeTranslate
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
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $context_key;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $template_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $trans_id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $trans_value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
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

    public function getTemplateId(): ?int
    {
        return $this->template_id;
    }

    public function setTemplateId(int $template_id): self
    {
        $this->template_id = $template_id;

        return $this;
    }

    public function getTransId(): ?string
    {
        return $this->trans_id;
    }

    public function setTransId(string $trans_id): self
    {
        $this->trans_id = $trans_id;

        return $this;
    }

    public function getTransValue()
    {
        if ( $this->is_json( $this->trans_value ) ) {
            $trans_value = json_decode( $this->trans_value, true );
        } else {
            $trans_value = $this->trans_value;
        }
        return $trans_value;
    }

    public function setTransValue($trans_value): self
    {   
        if ( is_array( $trans_value ) ) {
            $this->trans_value = json_encode( $trans_value );
        } else {
            $this->trans_value = $trans_value;
        }
        return $this;
    }

    private function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
}
