<?php

namespace BackSystem\Base\Maker;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractMaker extends \Symfony\Bundle\MakerBundle\Maker\AbstractMaker
{
    public function __construct(private readonly KernelInterface $kernel, private readonly Filesystem $filesystem)
    {
    }

    protected function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    protected function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    protected function askFirstLevel(SymfonyStyle $io, string $directory, string $question): string
    {
        $values = [];

        $files = (new Finder())->in('./src'.$directory)->depth(0)->directories();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $values[] = $file->getBasename();
        }

        $random = !empty($values) ? $values[array_rand($values)] : 'User';

        $q = new Question($question.' (e.g. <fg=yellow>'.$random.'</>)');
        $q->setAutocompleterValues($values);

        do {
            $value = $io->askQuestion($q);
        } while (null === $value || !in_array($value, $values, true));

        return $value;
    }

    protected function askLastLevel(SymfonyStyle $io, string $directory, string $question): string
    {
        $values = [];

        if ($this->filesystem->exists('./src'.$directory)) {
            $files = (new Finder())->in('./src'.$directory)->depth(0)->files();
        } else {
            $files = [];
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $values[] = preg_replace('/\.php$/', '', $file->getBasename());
        }

        $random = !empty($values) ? $values[array_rand($values)] : 'User';

        $q = new Question($question.' (e.g. <fg=yellow>'.$random.'</>)');
        $q->setAutocompleterValues($values);

        do {
            $value = $io->askQuestion($q);
        } while (null === $value);

        return $value;
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function createTemplate(string $template, array $params, string $output): void
    {
        ob_start();

        extract($params);

        include __DIR__.'/templates/'.$template.'.tpl.php';

        $file = ob_get_clean();

        $filename = $this->kernel->getProjectDir().'/'.$output;
        $directory = dirname($filename);

        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created.', $directory));
        }

        file_put_contents($filename, $file);
    }
}
