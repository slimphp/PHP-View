<?php
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Response;

class PhpPassedFunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testPassedFunction() {
        $renderer = new \Slim\Views\PhpRenderer("tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplateFunction.php", array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }
}
