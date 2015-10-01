## PHP Renderer


## Usage

```php
$app = new Slim\App();
$container = $app->getContainer();
$container['phprenderer'] = function () {
    return new Geggleto\Renderer\PHPRenderer("./templates");
};

$app->get('/hello/{name}', function ($request, $response, $args) {
    //You will now have $name available in your template
    return $this->phprenderer->render($response, "/hello.php", $args);
});

$app->run();
```

## Exceptions
`\RuntimeException` - if template does not exist
`\InvalidArgumentException` - if $data contains 'template'