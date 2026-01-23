<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class Html5TagRewriterIntegrationTest extends KernelTestCase
{
    public function testRewritesWithDefaultHandler(): void
    {
        // Test using \Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler\TestDefaultHandler,
        // autowired as default through the `#[AsRewriteHandler]` attribute.

        $result = $this->renderTemplate('{% apply rewriteTags %}<p>hello world</p>{% endapply %}');

        self::assertSame('<p>test-default-handler</p>', $result);
    }

    public function testRewritesWithSpecialHandler(): void
    {
        // Test using \Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler\TestSpecialHandler,
        // configured and tagged in the container.

        $result = $this->renderTemplate('{% apply rewriteTags("special") %}<p>hello world</p>{% endapply %}');

        self::assertSame('<p>test-special-handler</p>', $result);
    }

    private function renderTemplate(string $twigMarkup): string
    {
        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $template = $twig->createTemplate($twigMarkup);

        return $template->render();
    }
}
