<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler;

use Dom\Element;
use Webfactory\Html5TagRewriter\Handler\BaseRewriteHandler;
use Webfactory\Html5TagRewriterBundle\Attribute\AsRewriteHandler;

#[AsRewriteHandler]
class TestDefaultHandler extends BaseRewriteHandler
{
    public function appliesTo(): string
    {
        return '//html:p';
    }

    public function match(Element $element): void
    {
        $element->textContent = 'test-default-handler';
    }
}
