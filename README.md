## Usage

```
$app = new Slim\App();
$container = $app->getContainer();
$container['renderer'] = new SlimRenderer("./templates");

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->renderer->render($response, "/hello.php");
});

$app->run();
```