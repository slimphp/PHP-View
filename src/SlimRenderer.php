<?php
/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-09-29
 * Time: 1:14 PM
 */

namespace Slim;


use Psr\Http\Message\ResponseInterface;

class SlimRenderer
{
    protected $templatePath = "";

    public function __construct($templatePath = "") {
        $this->templatePath = $templatePath;
    }

    public function render(ResponseInterface $response, $template = "", $data = []) {
        extract($data);
        ob_start();
        include $this->templatePath . $template;
        $output = ob_get_clean();

        return $response->write($output);
    }
}