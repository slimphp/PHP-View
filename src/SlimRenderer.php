<?php
/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-09-29
 * Time: 1:14 PM
 */

namespace Geggleto;

use Slim\Http\Response;

/**
 * Class SlimRenderer
 *
 * @package Renderer
 */
class SlimRenderer
{
    /**
     * @var string
     */
    protected $templatePath = "";

    /**
     * SlimRenderer constructor.
     *
     * @param string $templatePath
     */
    public function __construct($templatePath = "")
    {
        $this->templatePath = $templatePath;
    }

    /**
     * Render
     *
     * Inserts template into Response object
     *
     * @param \Slim\Http\Response $response
     * @param                     $template
     * @param array               $data
     * @return \Slim\Http\Response
     */
    public function render(Response $response, $template, array $data = [])
    {

        extract($data);

        ob_start();
        include $this->templatePath . $template;
        $output = ob_get_clean();

        return $response->write($output);
    }
}