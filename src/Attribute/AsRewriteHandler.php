<?php

namespace Webfactory\Html5TagRewriterBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AsRewriteHandler
{
    /** @var list<string> */
    public array $rewriters;

    /**
     * @param string|list<string> $rewriter
     */
    public function __construct(
        public int $priority = 0,
        string|array $rewriter = 'default',
    ) {
        $this->rewriters = (array) $rewriter;
    }
}
