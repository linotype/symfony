<?php

namespace Linotype\Symfony\DataCollector;

use Linotype\Symfony\Service\LinotypeConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Linotype\Symfony\Core\Linotype;

final class LinotypeCollector extends DataCollector
{
    
    public function __construct(Linotype $linotype)
    {
        $this->linotype = $linotype;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->data = [
            'config' => [],//$this->linotype->getConfig(),
            'logs' => $this->linotype->getLogs(),
        ];
    }

    public function getLinotype()
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'linotype';
    }

}