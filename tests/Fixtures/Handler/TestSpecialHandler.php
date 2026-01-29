<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler;

use Dom\Element;
use Dom\Node;
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

    public function match(Node $node): void
    {
        assert($node instanceof Element);
        $node->textContent = $this->content;
    }
}
