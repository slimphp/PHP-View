<?php
/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-09-29
 * Time: 1:14 PM
 */

namespace Geggleto\Renderer;

use Slim\Http\Response;

/**
 * Class SlimRenderer
 * PSR-7 compatible PHP Renderer
 *
 * @package Geggleto\Renderer
 */
class PHPRenderer
{
    /**
     * @var string
     */
    protected $templatePath;

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
     * @param string $template
     * @param array $data
     * @return \Slim\Http\Response
     */
    public function render(Response $response, $template, array $data = [])
    {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        extract($data);

        ob_start();
        include $this->templatePath . $template;
        $output = ob_get_clean();

        return $response->write($output);
    }
}