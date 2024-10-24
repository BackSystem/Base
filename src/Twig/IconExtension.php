<?php

namespace BackSystem\Base\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IconExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', $this->icon(...), ['is_safe' => ['html']]),
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
    }
}
