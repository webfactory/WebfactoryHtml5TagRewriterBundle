<?php

namespace Webfactory\Html5TagRewriterBundle\Twig;

use Override;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Webfactory\Html5TagRewriter\TagRewriter;

final class Extension extends AbstractExtension
{
    private ?TagRewriter $defaultTagRewriter = null;

    /** @var array<string, TagRewriter> */
    private array $namedTagRewriter = [];

    public function setDefaultTagRewriter(TagRewriter $defaultTagRewriter): void
    {
        $this->defaultTagRewriter = $defaultTagRewriter;
    }

    public function addTagRewriter(string $name, TagRewriter $tagRewriter): void
    {
        if (null === $this->defaultTagRewriter) {
            $this->defaultTagRewriter = $tagRewriter;
        }

        $this->namedTagRewriter[$name] = $tagRewriter;
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('rewriteTags', $this->rewriteTags(...), ['is_safe' => ['html']]),
        ];
    }

    public function rewriteTags(string $html, ?string $name = null): string
    {
        if (null === $name) {
            $tagRewriter = $this->defaultTagRewriter;
        } else {
            if (!isset($this->namedTagRewriter[$name])) {
                throw new RuntimeException("Unknown TagRewriter '$name'");
            }
            $tagRewriter = $this->namedTagRewriter[$name];
        }

        if (null === $tagRewriter) {
            throw new RuntimeException('No TagRewriter registered.');
        }

        return $tagRewriter->processBodyFragment($html);
    }
}
