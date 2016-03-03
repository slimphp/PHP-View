## PHP Renderer

This is a renderer for rendering PHP view scripts into a PSR-7 Response object. It works well with Slim Framework 3.


## Templates
You may use `$this` inside your php templates. `$this` will be the actual PhpRenderer object will allow you to render sub-templates

## Installation

Install with [Composer](http://getcomposer.org):

    composer require slim/php-view


## Usage With Slim 3

```php
use Slim\Views\PhpRenderer;

include "vendor/autoload.php";

$app = new Slim\App();
$container = $app->getContainer();
$container['renderer'] = new PhpRenderer("./templates");

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->renderer->render($response, "/hello", $args);
});

$app->run();
```

The view file name extension can be ommitted when it's _.php_.

## Usage with any PSR-7 Project
```php
//Construct the View
$phpView = new PhpRenderer("./path/to/templates");

//Render a Template
$response = $phpView->render(new Response(), "/path/to/template.php", $yourData);
```

## Using layouts

A layout file can be specified in the constructor so that views are rendered inside it

```php
$container['renderer'] = new PhpRenderer("./templates", "my_layout");
```

Layouts are located in the same path as templates.

The layout can be changed any time with:

```php
$container['renderer']->setLayout('other_layout');
```

Or set it to _null_ to stop using a layout.

## Exceptions
`\RuntimeException` - if template does not exist

`\InvalidArgumentException` - if $data contains 'template'
