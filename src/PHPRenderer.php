<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/Twig-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 */

namespace Geggleto\Renderer;

use Psr\Http\Message\ResponseInterface;

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
     * Render a template
     *
     * $data cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param \Slim\Http\Response $response
     * @param                     $template
     * @param array               $data
     *
     * @return \Slim\Http\Response
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $template, array $data = [])
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

        return $response->getBody()->write($output);
    }
}