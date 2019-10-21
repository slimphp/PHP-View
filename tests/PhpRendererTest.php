<?php
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Response;

/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-11-12
 * Time: 1:19 PM
 */
class PhpRendererTest extends PHPUnit_Framework_TestCase
{

    public function testRenderer() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testRenderConstructor() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testAttributeMerging() {

        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/", [
            "hello" => "Hello"
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", [
            "hello" => "Hi"
        ]);
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testExceptionInTemplate() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "testException.php");
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        } catch (Exception $e) { // PHP < 7
            // Simulates an error template
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionForTemplateInData() {

        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "template.phtml", [
            "template" => "Hi"
        ]);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testTemplateNotFound() {

        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "adfadftemplate.phtml", []);
    }

    public function testLayout() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/", ["title" => "My App"]);
        $renderer->setLayout("layout.phtml");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", array("title" => "Hello - My App", "hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer</footer></body></html>", $newResponse->getBody()->getContents());
    }

    public function testLayoutConstructor() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files", ["title" => "My App"], "layout.phtml");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", array("title" => "Hello - My App", "hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer</footer></body></html>", $newResponse->getBody()->getContents());
    }

    public function testExceptionInLayout() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");
        $renderer->setLayout("exception_layout.phtml");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "template.phtml");
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $renderer->setLayout(null);
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        } catch (Exception $e) { // PHP < 7
            // Simulates an error template
            $renderer->setLayout(null);
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLayoutNotFound() {

        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");
        $renderer->setLayout("non-existent_layout.phtml");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "template.phtml", []);
    }

    public function testContentDataKeyShouldBeIgnored() {
        $renderer = new \Slim\Views\PhpRenderer(__DIR__ . "/_files/");
        $renderer->setLayout("layout.phtml");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", array("title" => "Hello - My App", "hello" => "Hi", "content" => "Ho"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer</footer></body></html>", $newResponse->getBody()->getContents());
    }
}
