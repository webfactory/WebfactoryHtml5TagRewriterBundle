<?php

namespace Webfactory\Html5TagRewriterBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Webfactory\Html5TagRewriterBundle\Attribute\AsRewriteHandler;
use Webfactory\Html5TagRewriterBundle\DependencyInjection\Compiler\TagRewriterPass;

final class WebfactoryHtml5TagRewriterExtension extends Extension
{
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container->registerAttributeForAutoconfiguration(
            AsRewriteHandler::class,
            TagRewriterPass::autoconfigureFromAttribute(...)
        );
    }
}
