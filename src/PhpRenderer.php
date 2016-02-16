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
 * Class PhpRenderer
 * @package Slim\Views
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
     * @param ResponseInterface $response
     * @param string             $template
     * @param array              $data
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $output = $this->fetch($template, $data);

        $response->getBody()->write($output);
        
        return $response;
    }

    /**
     * Renders a template and returns the result as a string
     *
     * cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param $template
     * @param array $data
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function fetch($template, array $data = []) {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        ob_start();
        $this->protectedIncludeScope($this->templatePath . $template, $data);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * @param string $template
     * @param array $data
     */
    protected function protectedIncludeScope ($template, array $data) {
        extract($data);
        include $template;
    }
}
