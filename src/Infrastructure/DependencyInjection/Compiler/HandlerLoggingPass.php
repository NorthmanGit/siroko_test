<?php

namespace App\Infrastructure\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

final class HandlerLoggingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('logger')) {
            return;
        }

        $loggerRef = new Reference('logger');

        // Find all handlers tagged with 'app.handler'
        foreach ($container->findTaggedServiceIds('app.handler') as $id => $tags) {
            // The handler definition we want to decorate
            $definition = $container->getDefinition($id);

            // Create a unique ID for the decorator
            $decoratorId = $id . '.logging_decorator';

            $decorator = new Definition(\App\Infrastructure\LoggingQueryHandlerDecorator::class);
            $decorator->setDecoratedService($id);
            $decorator->setArgument('$inner', new Reference($decoratorId . '.inner'));
            $decorator->setArgument('$logger', $loggerRef);

            // You can leave this; it lets autowiring work for any other deps
            $decorator->setAutowired(true);

            $container->setDefinition($decoratorId, $decorator);
        }
    }
}
