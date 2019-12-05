<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/PHP-View/blob/3.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Views;

use InvalidArgumentException;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Exception\PhpTemplateNotFoundException;
use Throwable;

use function rtrim, ltrim, is_file, ob_start, ob_end_clean, ob_get_clean, extract;

use const PHP_EOL, EXTR_SKIP;

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
    /**
     * @param string $templatePath
     * @param array  $attributes
     * @param string $layout
     */
    public function __construct(string $templatePath = '', array $attributes = [], string $layout = '')
    {
        $this->setTemplatePath($templatePath);
        $this->setAttributes($attributes);
        $this->setLayout($layout);
    }

    /**
     * @param ResponseInterface $response
     * @param string            $template
     * @param array             $data
     *
     * @return ResponseInterface
     *
     * @throws Throwable
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $output = $this->fetch($template, $data, true);
        $response->getBody()->write($output);
        return $response;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }
    
    /**
     * @param string $layout
     */
    public function setLayout(string $layout): void
    {
        if ($layout) {
            $layout = ltrim($layout, '\/');
            if (! is_file($this->templatePath . $layout)) {
                throw new PhpTemplateNotFoundException('Layout template "' . $layout . '" does not exist');
            }
        }
        $this->layout = $layout;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function addAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = false)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = rtrim($templatePath, '\/') . PHP_EOL;
    }

    /**
     * @param string $template
     * @param array  $data
     * @param bool   $useLayout
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetch(string $template, array $data = [], bool $useLayout = false): string
    {
        $output = $this->fetchTemplate($template, $data);
        if ($this->layout && $useLayout) {
            $data['content'] = $output;
            $output = $this->fetchTemplate($this->layout, $data);
        }

        return $output;
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetchTemplate(string $template, array $data = []): string
    {
        if (isset($data['template'])) {
            throw new InvalidArgumentException('Duplicate template key found');
        }
        
        $template = ltrim($template, '\/');
        if (! is_file($this->templatePath . $template)) {
            throw new PhpTemplateNotFoundException(
                'View cannot render "' . $template . '" because the template does not exist'
            );
        }

        $data += $this->attributes;
        ob_start();
        try {
            $this->protectedIncludeScope($this->templatePath . $template, $data);
        } catch (Throwable $e) {
            ob_end_clean();
            $message = 'Error at rendering template "' . $template . '": ' . $e->getMessage();
            throw new RuntimeException($message, 0, $e);
        }

        return ob_get_clean();
    }

    /**
     * @param string $php_view_template
     * @param array  $php_view_data
     *
     * @return void
     */
    protected function protectedIncludeScope(string $php_view_template, array $php_view_data): void
    {
        extract($php_view_data, EXTR_SKIP);
        include $php_view_template;
    }
}
