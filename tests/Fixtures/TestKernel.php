<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Webfactory\Html5TagRewriterBundle\DependencyInjection\Compiler\TagRewriterPass;
use Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler\TestSpecialHandler;
use Webfactory\Html5TagRewriterBundle\WebfactoryHtml5TagRewriterBundle;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new WebfactoryHtml5TagRewriterBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ] + (Kernel::VERSION_ID < 70000 ? ['annotations' => ['enabled' => false]] : []));

        $services = $container->services();

        $services->defaults()
                ->autowire()
                ->autoconfigure();

        $services->load('Webfactory\\Html5TagRewriterBundle\\Tests\\Fixtures\\Handler\\', __DIR__.'/Handler/');

        $services->set(TestSpecialHandler::class)
            ->arg('$content', 'test-special-handler')
            ->tag(TagRewriterPass::TAG, ['rewriter' => 'special']);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
