## Usage

```php
$app = new Slim\App();
$container = $app->getContainer();
$container['renderer'] = function () {
    return new Geggleto\Renderer\SlimRenderer("./templates");
};

$app->get('/hello/{name}', function ($request, $response, $args) {
    //You will now have $name available in your template
    return $this->renderer->render($response, "/hello.php", $args);
});

$app->run();
```