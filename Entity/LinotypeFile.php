<?php

namespace Linotype\Bundle\LinotypeBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinotypeFileRepository::class)
 * @Gedmo\Uploadable( allowOverwrite=true)
 */
class LinotypeFile
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
 
    /**
     * @ORM\Column(name="path", type="string", nullable=true)
     * @Gedmo\UploadableFilePath
     */
    private $path;
     
     /**
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\UploadableFileName
     */
    private $name;
 
    public function getId()
    {
        return $this->id;
    }
 
    public function setPath($path)
    {
        $this->path = $path;
     
        return $this;
    }
 
    public function getPath()
    {
        return $this->path;
    }
 
    public function setName($name)
    {
        $this->name = $name;
     
        return $this;
    }
 
    public function getName()
    {
        return $this->name;
    }

}
