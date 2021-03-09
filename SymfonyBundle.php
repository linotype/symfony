<?php

namespace Linotype\SymfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Linotype\SymfonyBundle\Core\Linotype;
use Linotype\SymfonyBundle\DependencyInjection\LinotypeExtension;

class SymfonyBundle extends Bundle 
{
    public function build(ContainerBuilder $container)
    {
        //build
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new LinotypeExtension();
        }
        return $this->extension;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

}