<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Twig;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;
use Webfactory\Html5TagRewriter\TagRewriter;
use Webfactory\Html5TagRewriterBundle\Twig\Extension;

class ExtensionTest extends TestCase
{
    private Extension $extension;

    private TagRewriter&Stub $tagRewriter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new Extension();
        $this->tagRewriter = $this->createStub(TagRewriter::class);
    }

    public function testFilterUsesDefaultRewriter(): void
    {
        $this->tagRewriter->method('processBodyFragment')
            ->with('<p>input</p>')
            ->willReturn('<p class="processed">output</p>');

        $this->extension->setDefaultTagRewriter($this->tagRewriter);

        $this->assertHtmlRewritesTo('<p class="processed">output</p>', '<p>input</p>');
    }

    public function testFilterUsesNamedRewriter(): void
    {
        $this->tagRewriter->method('processBodyFragment')
            ->with('<p>input</p>')
            ->willReturn('<p class="processed">output</p>');

        $this->extension->addTagRewriter('special', $this->tagRewriter);

        $this->assertHtmlRewritesTo('<p class="processed">output</p>', '<p>input</p>', 'special');
    }

    public function testFilterUsesOnlyDefaultRewriter(): void
    {
        $defaultRewriter = $this->createStub(TagRewriter::class);
        $defaultRewriter->method('processBodyFragment')->with('something')->willReturn('default');
        $otherRewriter = $this->createStub(TagRewriter::class);
        $otherRewriter->method('processBodyFragment')->willReturn('other');

        $this->extension->setDefaultTagRewriter($defaultRewriter);
        $this->extension->addTagRewriter('other-rewriter', $otherRewriter);

        $this->assertHtmlRewritesTo('default', 'something');
    }

    public function testFilterUsesOnlySpecialRewriter(): void
    {
        $defaultRewriter = $this->createStub(TagRewriter::class);
        $defaultRewriter->method('processBodyFragment')->willReturn('default');
        $otherRewriter = $this->createStub(TagRewriter::class);
        $otherRewriter->method('processBodyFragment')->with('something')->willReturn('other');

        $this->extension->setDefaultTagRewriter($defaultRewriter);
        $this->extension->addTagRewriter('other-rewriter', $otherRewriter);

        $this->assertHtmlRewritesTo('other', 'something', 'other-rewriter');
    }

    public function testFilterThrowsWhenRewriterIsUnknownEvenIfDefaultAvailable(): void
    {
        $defaultRewriter = $this->createStub(TagRewriter::class);
        $this->extension->setDefaultTagRewriter($defaultRewriter);

        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessageMatches("/Unknown TagRewriter 'some-rewriter'/");
        $this->runRewriteTagsFilter('test', 'some-rewriter');
    }

    public function testFilterThrowsWhenNoRewriterIsAvailable(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessageMatches('/No TagRewriter registered/');
        $this->runRewriteTagsFilter('test');
    }

    private function runRewriteTagsFilter(string $html, ?string $rewriter = null): string
    {
        $twig = new Environment(new ArrayLoader([
            'test-template' => '{{ html|rewriteTags('.($rewriter ? "'$rewriter'" : '').') }}',
        ]));
        $twig->addExtension($this->extension);

        return $twig->render('test-template', ['html' => $html]);
    }

    private function assertHtmlRewritesTo(string $expected, string $html, ?string $rewriter = null): void
    {
        self::assertSame($expected, $this->runRewriteTagsFilter($html, $rewriter));
    }
}
