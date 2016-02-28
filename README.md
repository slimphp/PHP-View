## PHP Renderer

This is a renderer for rendering PHP view scripts into a PSR-7 Response object. It works well with Slim Framework 3.

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
    return $this->renderer->render($response, "/hello.php", $args);
});

$app->run();
```

## Usage with any PSR-7 Project
```php
//Construct the View
$phpView = new PhpRenderer("./path/to/templates");

//Render a Template
$response = $phpView->render(new Response(), "/path/to/template.php", $yourData);
```

## Exceptions
`\RuntimeException` - if template does not exist

`\InvalidArgumentException` - if $data contains 'template'

## Template Syntax

You may use most normal PHP syntax, with one added function: `$render($template, $data)`.
Where `$template` is a string for the template name(with file extension), and $data is an optional array of data to pass to the template.
The `$render()` function renders an additional template.
Note that the `$` is necessary.
Example **In Template**
```php
echo "Hello ";
$render("world.php", ["myvar"=>"world"]);
```
**In world.php**
```php
echo $myvar;
```
**Output**
`Hello world`
