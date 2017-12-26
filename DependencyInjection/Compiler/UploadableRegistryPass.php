<?php

namespace KRG\FileBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class UploadableRegistryPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container) {
        $this->load($container, 'emc.file.uploadable.registry', 'emc.file.uploadable');
    }
    
    private function load(ContainerBuilder $container, $service, $tag) {
        if (!$container->hasDefinition($service)) {
            return;
        }

        $definition = $container->getDefinition($service);

        // Builds an array with service IDs as keys and tag aliases as values
        $services = array();

        foreach ($container->findTaggedServiceIds($tag) as $id => $config) {
            $alias = isset($config[0]['alias']) ? $config[0]['alias'] : $id;
            $services[$alias] = $id;
        }

        $definition->replaceArgument(0, $services);
    }
}
