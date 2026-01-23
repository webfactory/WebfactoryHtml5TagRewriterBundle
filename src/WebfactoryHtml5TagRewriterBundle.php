<?php

namespace Webfactory\Html5TagRewriterBundle;

use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webfactory\Html5TagRewriterBundle\DependencyInjection\Compiler\TagRewriterPass;

/** @psalm-suppress MissingConstructor */
final class WebfactoryHtml5TagRewriterBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TagRewriterPass());
    }
}
