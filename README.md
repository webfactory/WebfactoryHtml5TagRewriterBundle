# WebfactoryHtml5TagRewriterBundle

This bundle provides Symfony integration for the [webfactory/html5-tagrewriter](https://github.com/webfactory/html5-tagrewriter) package.

Core feature is the Twig filter `rewriteTags`, which allows sections of HTML5 in Twig templates to be processed by RewriteHandlers. RewriteHandlers can be configured in the container through tags or using an autoconfiguration attribute.

See the [webfactory/html5-tagrewriter documentation](https://github.com/webfactory/html5-tagrewriter) for details on what a RewriteHandler is and how to implement one. Processing is based on the PHP 8.4 DOM HTML5 parser (`Dom\HTMLDocument`).

## Usage in Twig

Use the `rewriteTags` filter to process HTML through a TagRewriter:

```twig
{# With the apply tag #}
{% apply rewriteTags %}
    <a href="https://example.com">Link</a>
{% endfilter %}

{# ... or the pipe notation #}
{{ content|rewriteTags }}

{# Using a named TagRewriter #}
{{ content|rewriteTags('special') }}
```

## Registering RewriteHandlers

### Using the `#[AsRewriteHandler]` Attribute (Recommended)

The easiest way to register a RewriteHandler is using the `#[AsRewriteHandler]` attribute. With autowiring and autoconfiguration enabled, the handler will be automatically registered:

```php
use Webfactory\Html5TagRewriter\Handler\BaseRewriteHandler;
use Webfactory\Html5TagRewriterBundle\Attribute\AsRewriteHandler;
use Dom\Element;

#[AsRewriteHandler]
class MyRewriteHandler extends BaseRewriteHandler
{
    public function appliesTo(): string
    {
        return '//html:a';
    }

    public function match(Element $element): void
    {
        $element->setAttribute('target', '_blank');
    }
}
```

### Attribute Options

The `#[AsRewriteHandler]` attribute accepts two optional parameters:

- `priority` (int, default: `0`): Higher values are processed first
- `rewriter` (string or array, default: `'default'`): The name(s) of the TagRewriter instance(s) to register with

```php
// Register with high priority on the default rewriter
#[AsRewriteHandler(priority: 100)]
class HighPriorityHandler extends BaseRewriteHandler { /* ... */ }

// Register on a named rewriter
#[AsRewriteHandler(rewriter: 'special')]
class SpecialHandler extends BaseRewriteHandler { /* ... */ }

// Register on multiple rewriters
#[AsRewriteHandler(rewriter: ['default', 'special'])]
class SharedHandler extends BaseRewriteHandler { /* ... */ }

// Combine priority and named rewriter
#[AsRewriteHandler(priority: 50, rewriter: 'special')]
class PrioritizedSpecialHandler extends BaseRewriteHandler { /* ... */ }
```

### Using Service Tags

If you need more control or cannot use autoconfiguration, you can manually tag your services with `webfactory.html5_tag_rewriter.rewrite_handler`:

```php
// config/services.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(App\Handler\MyRewriteHandler::class)
        // ... arguments or other configuration here
        ->tag('webfactory.html5_tag_rewriter.rewrite_handler');

    $services->set(App\Handler\SpecialHandler::class)
        // ... arguments or other configuration here
        ->tag('webfactory.html5_tag_rewriter.rewrite_handler', [
            'rewriter' => 'special',
            'priority' => 10,
        ]);
};
```

## Credits, Copyright and License

This bundle is based on internal work that we have been using at webfactory GmbH, Bonn, since 2019. 
However, that (old) bundle implementation was written for the legacy PHP DOM extension, leading to 
several quirks in HTML processing and requiring the use of [Polyglot HTML 5](https://www.w3.org/TR/html-polyglot/).

Thus, we decided to overhaul the bundle for PHP 8.4 HTML5 DOM support and re-release it as open source.

- <https://www.webfactory.de>

Copyright 2026 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).   
