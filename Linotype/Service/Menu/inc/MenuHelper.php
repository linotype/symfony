<?php

namespace Linotype\Bundle\SymfonyBundle\Linotype\Service\Menu\inc;

class MenuHelper {

  public function format( $data )
  {
    return ucwords( $data );
  }

}
