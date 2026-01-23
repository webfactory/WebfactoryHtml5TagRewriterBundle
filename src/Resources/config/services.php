<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webfactory\Html5TagRewriter\Implementation\Html5TagRewriter;
use Webfactory\Html5TagRewriter\TagRewriter;
use Webfactory\Html5TagRewriterBundle\Twig\Extension;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(Extension::class)
        ->tag('twig.extension');

    // Prototype for TagRewriter instances (used by compiler pass)
    $services->set('webfactory.html5_tag_rewriter.prototype', Html5TagRewriter::class)
        ->abstract();

    // Default TagRewriter (will be configured by compiler pass)
    $services->set('webfactory.html5_tag_rewriter.instance.default', Html5TagRewriter::class)
        ->lazy()
        ->tag('proxy', ['interface' => TagRewriter::class]);

    // Autowiring alias: TagRewriter interface -> default rewriter
    $services->alias(TagRewriter::class, 'webfactory.html5_tag_rewriter.instance.default');
};
