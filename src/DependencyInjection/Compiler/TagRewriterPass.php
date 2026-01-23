<?php

namespace Webfactory\Html5TagRewriterBundle\DependencyInjection\Compiler;

use Override;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Webfactory\Html5TagRewriter\TagRewriter;
use Webfactory\Html5TagRewriterBundle\Attribute\AsRewriteHandler;
use Webfactory\Html5TagRewriterBundle\Twig\Extension;

final class TagRewriterPass implements CompilerPassInterface
{
    public const TAG = 'webfactory.html5_tag_rewriter.rewrite_handler';

    /** @var array<string, list<Reference>> */
    private array $handlersByRewriter = [];

    public static function autoconfigureFromAttribute(
        ChildDefinition $definition,
        AsRewriteHandler $attribute,
    ): void {
        foreach ($attribute->rewriters as $rewriter) {
            $definition->addTag(self::TAG, [
                'priority' => $attribute->priority,
                'rewriter' => $rewriter,
            ]);
        }
    }

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $this->collectRewriteHandlers($container);
        $this->setupTagRewriters($container);
    }

    private function collectRewriteHandlers(ContainerBuilder $container): void
    {
        $handlers = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $rewriter = $tag['rewriter'] ?? 'default';
                $priority = $tag['priority'] ?? 0;

                $handlers[$rewriter][$priority][] = new Reference($id);
            }
        }

        // Sort by priority (descending) and flatten
        foreach ($handlers as $rewriter => $handlersByPriority) {
            krsort($handlersByPriority);
            $this->handlersByRewriter[$rewriter] = array_merge(...array_values($handlersByPriority));
        }
    }

    private function setupTagRewriters(ContainerBuilder $container): void
    {
        $twigExtension = $container->getDefinition(Extension::class);
        $prototype = $container->getDefinition('webfactory.html5_tag_rewriter.prototype');

        foreach ($this->handlersByRewriter as $rewriterName => $handlers) {
            $serviceId = 'webfactory.html5_tag_rewriter.instance.'.$rewriterName;

            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
            } else {
                $definition = clone $prototype;
                $definition->setAbstract(false);
                $definition->setLazy(true);
                $container->setDefinition($serviceId, $definition);
            }

            foreach ($handlers as $handler) {
                $definition->addMethodCall('register', [$handler]);
            }

            $twigExtension->addMethodCall('addTagRewriter', [$rewriterName, new Reference($serviceId)]);

            $container->setAlias(TagRewriter::class.' $'.$rewriterName, $serviceId);
        }

        // Ensure default rewriter is registered in Twig extension
        if ($container->hasDefinition('webfactory.html5_tag_rewriter.instance.default')) {
            $twigExtension->addMethodCall('setDefaultTagRewriter', [new Reference('webfactory.html5_tag_rewriter.instance.default')]);
        }
    }
}
