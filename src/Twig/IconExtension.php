<?php

namespace BackSystem\Base\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IconExtension extends AbstractExtension
{
    // public function __construct(private readonly ContainerInterface $container)
    // {
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, string> $attributes
     */
    public function icon(string $path, array $attributes = []): string
    {
        $explode = explode('/', $path);

        $attributes['class'] = trim(sprintf('fa-%s fa-fw fa-%s %s', $explode[0], $explode[1], $attributes['class'] ?? ''));

        $content = '<i';

        foreach ($attributes as $key => $value) {
            $content .= sprintf(' %s="%s"', $key, $value);
        }

        $content .= '></i>';

        return $content;

        // /** @var string $base */
        // $base = $this->container->getParameter('kernel.project_dir');
        //
        // $content = @file_get_contents($base.'/node_modules/@fortawesome/fontawesome-free/svgs/'.$path.'.svg');
        //
        // if (!$content) {
        //     @trigger_error('Unknown icon: '.$path, E_USER_DEPRECATED);
        //
        //     return '';
        // }
        //
        // $replaced = preg_replace('/<!--(.|\s)*?-->/', '', $content);
        //
        // if (is_string($replaced)) {
        //     $content = $replaced;
        // }
        //
        // $attributes = array_reverse($attributes);
        //
        // foreach ($attributes as $key => $value) {
        //     $replaced = preg_replace('(viewBox="[0-9 ]+")', '$0 '.$key.'="'.$value.'"', $content);
        //
        //     if (is_string($replaced)) {
        //         $content = $replaced;
        //     }
        // }
        //
        // return str_replace('<path ', '<path fill="currentColor" ', $content);
    }
}
