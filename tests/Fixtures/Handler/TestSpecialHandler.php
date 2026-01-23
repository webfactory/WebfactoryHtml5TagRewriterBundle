<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler;

use Dom\Element;
use Webfactory\Html5TagRewriter\Handler\BaseRewriteHandler;

class TestSpecialHandler extends BaseRewriteHandler
{
    public function __construct(
        private readonly string $content,
    ) {
    }

    public function appliesTo(): string
    {
        return '//html:p';
    }

    public function match(Element $element): void
    {
        $element->textContent = $this->content;
    }
}
