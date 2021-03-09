<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Linotype\Bundle\Core\Linotype;

class UserAgentSubscriber implements EventSubscriberInterface
{
    public function __construct(Linotype $linotype)
    {
        $this->linotype = $linotype;
    }

    public function onKernelRequest()
    {
        $this->linotype->log('onKernelRequest');
    }

    public function onKernelTerminate()
    {
        $this->linotype->log('onKernelTerminate');
    }

    public static function getSubscribedEvents()
    {
        return array(
            'kernel.request' => 'onKernelRequest',
            'kernel.terminate' => 'onKernelTerminate',
        );
    }

    

}