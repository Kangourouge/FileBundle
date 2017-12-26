<?php

namespace KRG\FileBundle\DependencyInjection\Compiler;

use KRG\FileBundle\Filesystem\Adapter\Local;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GaufretteCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('knp_gaufrette.adapter.local')->setClass(Local::class);
    }
}