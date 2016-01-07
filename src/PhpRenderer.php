<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/PHP-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 */
namespace Slim\Views;

use Psr\Http\Message\ResponseInterface;

/**
 * Php View
 *
 * Render PHP view scripts into a PSR-7 Response object
 */
class PhpRenderer
{
    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var string
     */
    protected $layout;

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
     * Set layout template
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        if (!is_file($this->templatePath . $layout)) {
            throw new \RuntimeException("Layout template `$layout` does not exist");
        }
        $this->layout = $layout;
    }

    /**
     * Render a template
     *
     * $data cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param \ResponseInterface $response
     * @param                    $template
     * @param array              $data
     *
     * @return ResponseInterface
     *
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $render = function ($myTemplateVariableTooLongToBeReal, $data) {
            extract($data);
            include $myTemplateVariableTooLongToBeReal;
        };

        ob_start();
        $render($this->templatePath . $template, $data);
        $output = ob_get_clean(); 

        if ($this->layout) {
            ob_start();
            $data['content'] = $output;
            $render($this->templatePath . $this->layout, $data);
            $output = ob_get_clean(); 
        }

        $response->getBody()->write($output);

        return $response;
    }
}

