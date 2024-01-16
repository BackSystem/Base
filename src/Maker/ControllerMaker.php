<?php

namespace BackSystem\Base\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\String\Inflector\EnglishInflector;

use function Symfony\Component\String\u;

class ControllerMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:http-controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new HTTP controller class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $layer = $this->askFirstLevel($io, '/Http', 'Layer name of the controller to create');
        $controller = $this->askLastLevel($io, '/Http/'.$layer.'/Controller', 'Class name of the controller to create');

        if (!str_ends_with($controller, 'Controller')) {
            $controller .= 'Controller';
        }

        $name = substr($controller, 0, -10);

        $projectDir = $this->getKernel()->getProjectDir();

        $output = 'src/Http/'.$layer.'/Controller/'.$controller.'.php';

        if ($this->getFilesystem()->exists($projectDir.'/'.$output)) {
            $io->block(sprintf('The "%s" controller already exists.', $controller), null, 'fg=white;bg=red', ' ', true);

            return;
        }

        $inflector = new EnglishInflector();
        $pluralizedName = $inflector->pluralize($name)[0] ?? $name;

        $routeName = u($name)->snake().'_index';

        if ('Home' !== $layer) {
            $routeName = u($layer)->snake()->lower().'_'.$routeName;
        }

        $this->createTemplate('Controller', [
            'layer' => $layer,
            'controller' => $controller,
            'route_url' => u($pluralizedName)->snake()->replace('_', '-'),
            'route_name' => $routeName,
            'template_path' => u($layer)->snake()->lower().'/'.u($name)->snake().'/index.html.twig',
        ], $output);

        $title = ucfirst(strtolower(trim(preg_replace('/(?<! )[A-Z]/', ' $0', $pluralizedName) ?? '')));

        $this->createTemplate('Template', [
            'layer' => strtolower($layer),
            'title' => $title,
        ], 'templates/'.strtolower($layer).'/'.u($name)->snake().'/index.html.twig');

        $this->writeSuccessMessage($io);

        $io->text('Next: Open your new controller class and add some pages!');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
