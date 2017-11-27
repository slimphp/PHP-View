<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/PHP-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 */
namespace Slim\Views;

use \InvalidArgumentException;
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
     * @var string
     */
    protected $layoutPath;

    /**
     * @var string
     */
    protected $defaultLayout = 'default.phtml';

    /**
     * @var array
     */
    protected $attributes;

    /**
     * SlimRenderer constructor.
     *
     * @param string $templatePath
     * @param $layoutPath
     * @param array $attributes
     */
    public function __construct($templatePath = "", $layoutPath, $attributes = [])
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
        $this->layoutPath = rtrim($layoutPath, '/\\') .'/';
        $this->attributes = $attributes;
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
     * Get the attributes for the renderer
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the renderer
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Add an attribute
     *
     * @param $key
     * @param $value
     */
    public function addAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    /**
     * Retrieve an attribute
     *
     * @param $key
     * @return mixed
     */
    public function getAttribute($key) {
        if (!isset($this->attributes[$key])) {
            return false;
        }

        return $this->attributes[$key];
    }

    /**
     * Get the template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Get the layout path
     *
     * @return string
     */
    public function getLayoutPath()
    {
        return $this->layoutPath;
    }

    /**
     * Set the template path
     *
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
    }

    /**
     * Set layout path
     *
     * @param $layoutPath
     */
    public function setLayoutPath($layoutPath)
    {
        $this->layoutPath = rtrim($layoutPath, '/\\') . '/';
    }

    /**
     * Renders a template and returns the result as a string
     *
     * cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param $template
     * @param null $layout
     * @param array $data
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function fetch($template, $layout = null, array $data = []) {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        if($layout == null) {
            $layout = $this->defaultLayout;
        }


        /*
        foreach ($data as $k=>$val) {
            if (in_array($k, array_keys($this->attributes))) {
                throw new \InvalidArgumentException("Duplicate key found in data and renderer attributes. " . $k);
            }
        }
        */
        $data = array_merge($this->attributes, $data);

        try {
            ob_start();
            $this->protectedIncludeScope($this->templatePath . $template, $data);
            $content = ob_get_contents();
            $data = array_merge(['content'=> $content], $this->attributes);

            $this->protectedIncludeScope( $this->layoutPath . $layout, $data);
            $output = ob_get_clean();
        } catch(\Throwable $e) { // PHP 7+
            ob_end_clean();
            throw $e;
        } catch(\Exception $e) { // PHP < 7
            ob_end_clean();
            throw $e;
        }

        return $output;
    }


    /**
     * @param string $template
     * @param array $data
     */
    protected function protectedIncludeScope ($template, array $data) {
        extract($data);
        include func_get_arg(0);
    }
}
