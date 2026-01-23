<?php

namespace Webfactory\Html5TagRewriterBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Webfactory\Html5TagRewriter\Implementation\Html5TagRewriter;
use Webfactory\Html5TagRewriter\TagRewriter;
use Webfactory\Html5TagRewriterBundle\Attribute\AsRewriteHandler;
use Webfactory\Html5TagRewriterBundle\DependencyInjection\Compiler\TagRewriterPass;
use Webfactory\Html5TagRewriterBundle\Tests\Fixtures\Handler\TestDefaultHandler;
use Webfactory\Html5TagRewriterBundle\Twig\Extension;
use Webfactory\Html5TagRewriterBundle\WebfactoryHtml5TagRewriterBundle;

class TagRewriterPassTest extends TestCase
{
    public function testAutoconfigureFromAttributeWithDefaults(): void
    {
        $tags = self::processAttribute(new AsRewriteHandler());

        self::assertCount(1, $tags);
        self::assertSame(0, $tags[0]['priority']);
        self::assertSame('default', $tags[0]['rewriter']);
    }

    public function testAutoconfigureFromAttributeWithPriority(): void
    {
        $tags = self::processAttribute(new AsRewriteHandler(priority: 100));

        self::assertCount(1, $tags);
        self::assertSame(100, $tags[0]['priority']);
        self::assertSame('default', $tags[0]['rewriter']);
    }

    public function testAutoconfigureFromAttributeWithNamedRewriter(): void
    {
        $tags = self::processAttribute(new AsRewriteHandler(rewriter: 'special'));

        self::assertCount(1, $tags);
        self::assertSame(0, $tags[0]['priority']);
        self::assertSame('special', $tags[0]['rewriter']);
    }

    public function testAutoconfigureFromAttributeWithMultipleRewriters(): void
    {
        $tags = self::processAttribute(new AsRewriteHandler(priority: 50, rewriter: ['default', 'special']));

        self::assertCount(2, $tags);
        self::assertSame(50, $tags[0]['priority']);
        self::assertSame('default', $tags[0]['rewriter']);
        self::assertSame(50, $tags[1]['priority']);
        self::assertSame('special', $tags[1]['rewriter']);
    }

    /**
     * @return list<array{priority: int, rewriter: string}>
     */
    private static function processAttribute(AsRewriteHandler $attribute): array
    {
        $definition = new ChildDefinition('');

        TagRewriterPass::autoconfigureFromAttribute(
            $definition,
            $attribute,
        );

        return $definition->getTag(TagRewriterPass::TAG);
    }

    public function testProcessCreatesTagRewriterInstance(): void
    {
        $container = $this->createContainerWithHandler('handler1', 'default', 0);

        (new TagRewriterPass())->process($container);

        self::assertTrue($container->hasDefinition('webfactory.html5_tag_rewriter.instance.default'));
    }

    public function testTagRewriterInstancesAreLazy(): void
    {
        $container = $this->createContainer();
        $this->addHandler($container, 'handler1', 'default', 0);
        $this->addHandler($container, 'handler2', 'special', 0);

        (new TagRewriterPass())->process($container);

        self::assertTrue($container->getDefinition('webfactory.html5_tag_rewriter.instance.default')->isLazy());
        self::assertTrue($container->getDefinition('webfactory.html5_tag_rewriter.instance.special')->isLazy());
    }

    public function testProcessRegistersHandlerWithTagRewriter(): void
    {
        $container = $this->createContainerWithHandler('handler1', 'default', 0);

        (new TagRewriterPass())->process($container);

        $registerCalls = self::getMethodCalls($container, 'webfactory.html5_tag_rewriter.instance.default', 'register');
        self::assertCount(1, $registerCalls);
    }

    public function testProcessSortsHandlersByPriorityDescending(): void
    {
        $container = $this->createContainer();
        $this->addHandler($container, 'low_priority', 'default', 10);
        $this->addHandler($container, 'high_priority', 'default', 100);
        $this->addHandler($container, 'medium_priority', 'default', 50);

        (new TagRewriterPass())->process($container);

        $registerCalls = self::getMethodCalls($container, 'webfactory.html5_tag_rewriter.instance.default', 'register');
        self::assertCount(3, $registerCalls);

        // High priority (100) should be first
        self::assertEquals('high_priority', (string) $registerCalls[0][1][0]);
        // Medium priority (50) should be second
        self::assertEquals('medium_priority', (string) $registerCalls[1][1][0]);
        // Low priority (10) should be last
        self::assertEquals('low_priority', (string) $registerCalls[2][1][0]);
    }

    public function testProcessCreatesNamedAutowiringAlias(): void
    {
        $container = $this->createContainerWithHandler('handler1', 'default', 0);

        (new TagRewriterPass())->process($container);

        self::assertTrue($container->hasAlias(TagRewriter::class.' $default'));
        self::assertSame(
            'webfactory.html5_tag_rewriter.instance.default',
            (string) $container->getAlias(TagRewriter::class.' $default')
        );
    }

    public function testProcessCreatesMultipleRewriterInstances(): void
    {
        $container = $this->createContainer();
        $this->addHandler($container, 'handler1', 'default', 0);
        $this->addHandler($container, 'handler2', 'special', 0);

        (new TagRewriterPass())->process($container);

        self::assertTrue($container->hasDefinition('webfactory.html5_tag_rewriter.instance.default'));
        self::assertTrue($container->hasDefinition('webfactory.html5_tag_rewriter.instance.special'));

        self::assertSame(
            'webfactory.html5_tag_rewriter.instance.default',
            (string) $container->getAlias(TagRewriter::class.' $default')
        );
        self::assertSame(
            'webfactory.html5_tag_rewriter.instance.special',
            (string) $container->getAlias(TagRewriter::class.' $special')
        );
    }

    public function testProcessRegistersTwigExtensionMethodCalls(): void
    {
        $container = $this->createContainerWithHandler('handler1', 'default', 0);

        (new TagRewriterPass())->process($container);

        self::assertCount(1, self::getMethodCalls($container, Extension::class, 'addTagRewriter'));
        self::assertCount(1, self::getMethodCalls($container, Extension::class, 'setDefaultTagRewriter'));
    }

    public function testProcessSetsDefaultTagRewriterOnTwigExtension(): void
    {
        $container = $this->createContainerWithHandler('handler1', 'default', 0);

        (new TagRewriterPass())->process($container);

        $setDefaultCalls = self::getMethodCalls($container, Extension::class, 'setDefaultTagRewriter');
        self::assertCount(1, $setDefaultCalls);
        self::assertEquals('webfactory.html5_tag_rewriter.instance.default', (string) $setDefaultCalls[0][1][0]);
    }

    public function testDefaultRewriterExistsAndIsRegisteredEvenWithoutHandlers(): void
    {
        $container = $this->createContainer();

        (new TagRewriterPass())->process($container);

        // Default instance should still exist (defined in services.php)
        self::assertTrue($container->hasDefinition('webfactory.html5_tag_rewriter.instance.default'));

        // setDefaultTagRewriter should be called on Twig Extension
        self::assertCount(1, self::getMethodCalls($container, Extension::class, 'setDefaultTagRewriter'));
    }

    /**
     * @return list<array{0: string, 1: array<mixed>}>
     */
    private static function getMethodCalls(ContainerBuilder $container, string $serviceId, string $methodName): array
    {
        return array_values(array_filter(
            $container->getDefinition($serviceId)->getMethodCalls(),
            static fn ($call) => $call[0] === $methodName
        ));
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        // Twig Extension
        $container->setDefinition(Extension::class, new Definition(Extension::class));

        // Prototype
        $prototype = new Definition(Html5TagRewriter::class);
        $prototype->setAbstract(true);
        $container->setDefinition('webfactory.html5_tag_rewriter.prototype', $prototype);

        // Default instance (as defined in services.php)
        $default = new Definition(Html5TagRewriter::class);
        $default->setLazy(true);
        $container->setDefinition('webfactory.html5_tag_rewriter.instance.default', $default);

        return $container;
    }

    private function createContainerWithHandler(string $handlerId, string $rewriter, int $priority): ContainerBuilder
    {
        $container = $this->createContainer();
        $this->addHandler($container, $handlerId, $rewriter, $priority);

        return $container;
    }

    private function addHandler(ContainerBuilder $container, string $handlerId, string $rewriter, int $priority): void
    {
        $handler = new Definition(TestDefaultHandler::class);
        $handler->addTag(TagRewriterPass::TAG, [
            'rewriter' => $rewriter,
            'priority' => $priority,
        ]);
        $container->setDefinition($handlerId, $handler);
    }
}
