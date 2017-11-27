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
        $renderer = new \Slim\Views\PhpRenderer("tests/", "tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", false, array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testRenderTemplateInsideLayout()
    {
        $renderer = new \Slim\Views\PhpRenderer("tests/", "tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", 'testLayout.php', array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("<layout>Hi</layout>", $newResponse->getBody()->getContents());
    }

    public function testLayoutIsDisabled()
    {
        $renderer = new \Slim\Views\PhpRenderer("tests/", "tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", false, array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testRenderConstructor() {
        $renderer = new \Slim\Views\PhpRenderer("tests","tests");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", false, array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testAttributeMerging() {

        $renderer = new \Slim\Views\PhpRenderer("tests/", "tests/",[
            "hello" => "Hello"
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", false,[
            "hello" => "Hi"
        ]);
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testExceptionInTemplate() {
        $renderer = new \Slim\Views\PhpRenderer("tests/","tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "testException.php",false);
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $newResponse = $renderer->render($response, "testTemplate.php", false, [
                "hello" => "Hi"
            ]);
        } catch (Exception $e) { // PHP < 7
            // Simulates an error template
            $newResponse = $renderer->render($response, "testTemplate.php", false, [
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

        $renderer = new \Slim\Views\PhpRenderer("tests/","tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "testTemplate.php", false, [
            "template" => "Hi"
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionInlayout() {
        $renderer = new \Slim\Views\PhpRenderer("tests/","tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render($response, "testTemplate.php",'testException.php');
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $newResponse = $renderer->render($response, "testTemplate.php", false, [
                "hello" => "Hi"
            ]);
        } catch (Exception $e) { // PHP < 7
            // Simulates an error template
            $newResponse = $renderer->render($response, "testTemplate.php", false, [
                "hello" => "Hi"
            ]);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testTemplateNotFound() {

        $renderer = new \Slim\Views\PhpRenderer("tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "adfadftestTemplate.php", false, []);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLayoutNotFound() {

        $renderer = new \Slim\Views\PhpRenderer("tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render($response, "testTemplate.php",'adfadftestLayout.php', []);
    }
}
