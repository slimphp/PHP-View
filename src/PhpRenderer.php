<?php

declare(strict_types=1);

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
     * @var array
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $layout;

    public function __construct(string $templatePath = "", array $attributes = [], string $layout = "")
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
        $this->attributes = $attributes;
        $this->setLayout($layout);
    }

    /**
     * Render a template
     *
     * @note $data cannot contain template as a key
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException if $templatePath . $template does not exist
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $output = $this->fetch($template, $data, true);

        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Get layout template
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Set layout template
     */
    public function setLayout(string $layout): void
    {
        if ($layout === "" || $layout === null) {
            $this->layout = null;
        } else {
            $layoutPath = $this->templatePath . $layout;
            if (!is_file($layoutPath)) {
                throw new \RuntimeException("Layout template `$layout` does not exist");
            }
            $this->layout = $layout;
        }
    }

    /**
     * Get the attributes for the renderer
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the renderer
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Add an attribute
     */
    public function addAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Retrieve an attribute
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if (!isset($this->attributes[$key])) {
            return false;
        }

        return $this->attributes[$key];
    }

    /**
     * Get the template path
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Set the template path
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
    }

    /**
     * Renders a template and returns the result as a string
     *
     * @note $data cannot contain template as a key
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function fetch(string $template, array $data = [], bool $useLayout = false): string
    {
        $output = $this->fetchTemplate($template, $data);

        if ($this->layout !== null && $useLayout) {
            $data['content'] = $output;
            $output = $this->fetchTemplate($this->layout, $data);
        }

        return $output;
    }

    /**
     * Renders a template and returns the result as a string
     *
     * @note $data cannot contain template as a key
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function fetchTemplate(string $template, array $data = []): string
    {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $data = array_merge($this->attributes, $data);

        try {
            ob_start();
            $this->protectedIncludeScope($this->templatePath . $template, $data);
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
     * Include template within a separate scope for extracted $data
     */
    protected function protectedIncludeScope (string $template, array $data): void
    {
        extract($data);
        include func_get_arg(0);
    }
}
