<?php

declare(strict_types=1);

namespace Slim\ViewsTest;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Views\PhpRenderer;
use Throwable;

class PhpRendererTest extends TestCase
{

    public function testRenderer()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", ["hello" => "Hi"]);

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testRenderConstructor()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", ["hello" => "Hi"]);

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testAttributeMerging()
    {

        $renderer = new PhpRenderer(__DIR__ . "/_files/", [
            "hello" => "Hello"
        ]);

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", [
            "hello" => "Hi"
        ]);
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testExceptionInTemplate()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "exception_layout.phtml");
        } catch (Throwable $t) {
            // Simulates an error template
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testExceptionForTemplateInData()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $this->expectException(InvalidArgumentException::class);
        $renderer->render($response, "template.phtml", [
            "template" => "Hi"
        ]);
    }

    public function testTemplateNotFound()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $this->expectException(RuntimeException::class);
        $renderer->render($response, "adfadftemplate.phtml", []);
    }

    public function testLayout()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/", ["title" => "My App"]);
        $renderer->setLayout("layout.phtml");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", ["title" => "Hello - My App", "hello" => "Hi"]);

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer"
                            . "</footer></body></html>", $newResponse->getBody()->getContents());
    }

    public function testLayoutConstructor()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files", ["title" => "My App"], "layout.phtml");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "template.phtml", ["title" => "Hello - My App", "hello" => "Hi"]);

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer"
                            . "</footer></body></html>", $newResponse->getBody()->getContents());
    }

    public function testExceptionInLayout()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");
        $renderer->setLayout("exception_layout.phtml");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "template.phtml");
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $renderer->setLayout('');
            $newResponse = $renderer->render($response, "template.phtml", [
                "hello" => "Hi"
            ]);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testLayoutNotFound()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");
        $this->expectException(RuntimeException::class);
        $renderer->setLayout("non-existent_layout.phtml");
    }

    public function testContentDataKeyShouldBeIgnored()
    {
        $renderer = new PhpRenderer(__DIR__ . "/_files/");
        $renderer->setLayout("layout.phtml");

        $headers = new Headers();
        $body = new Stream(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render(
            $response,
            "template.phtml",
            ["title" => "Hello - My App", "hello" => "Hi", "content" => "Ho"]
        );

        $newResponse->getBody()->rewind();

        $this->assertEquals("<html><head><title>Hello - My App</title></head><body>Hi<footer>This is the footer"
                            . "</footer></body></html>", $newResponse->getBody()->getContents());
    }
}
