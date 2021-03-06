<?php

namespace Linotype\Bundle\LinotypeBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Linotype\Bundle\LinotypeBundle\DependencyInjection\LinotypeExtension;

class LinotypeBundle extends Bundle 
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

}