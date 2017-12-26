<?php

namespace KRG\FileBundle;

use KRG\FileBundle\DependencyInjection\Compiler\GaufretteCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use KRG\FileBundle\DependencyInjection\Compiler\UploadableRegistryPass;

class KRGFileBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new GaufretteCompilerPass());
        // $container->addCompilerPass(new UploadableRegistryPass());
    }

}
