<?php

namespace BackSystem\Base\Maker;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

class EntityMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:domain-entity';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new domain entity class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $domain = $this->askFirstLevel($io, '/Domain', 'Domain name of the entity to create or update');
        $entity = $this->askLastLevel($io, '/Domain/'.$domain.'/Entity', 'Class name of the entity to create or update');

        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $command = $application->find('make:entity');

        $arguments = [
            'command' => 'make:entity',
            'name' => "\\App\\Domain\\$domain\\Entity\\$entity",
        ];

        $greetInput = new ArrayInput($arguments);
        $output = $io->getOutput();

        $statusCode = $command->run($greetInput, $output);

        if (0 === $statusCode) {
            $projectDir = $this->getKernel()->getProjectDir();

            $output = 'src/Domain/'.$domain.'/Repository/'.$entity.'Repository.php';

            if (!$this->getFilesystem()->exists($projectDir.'/'.$output)) {
                $this->createTemplate('Repository', [
                    'domain' => $domain,
                    'entity' => $entity,
                ], $output);

                $this->getFilesystem()->remove($projectDir.'/src/Repository');

                $io->block(sprintf('The "%s" repository was successfully generated.', $entity.'Repository'), null, 'fg=white;bg=green', ' ', true);
            }

            $this->formatEntity($domain, $entity);
        }
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    private function formatEntity(string $domain, string $entity): void
    {
        $filename = "./src/Domain/$domain/Entity/$entity.php";

        $content = file_get_contents($filename);

        if (!$content) {
            return;
        }

        // Replace repository's namespace by correct
        $content = str_replace("use App\\Repository\\Domain\\$domain\\Entity\\{$entity}Repository;", "use App\\Domain\\$domain\\Repository\\{$entity}Repository;", $content);

        // Replace id property to readonly
        $content = str_replace('private ?int $id = null;', 'private readonly int $id;', $content);

        // Replace id getter
        $content = str_replace('public function getId(): ?int', 'public function getId(): int', $content);

        // Remove " = null;" from each property
        $content = str_replace(' = null;', ';', $content);

        // Place brace's function at the end of the line
        $content = preg_replace('/( {4})(.*)\n( +)?{/', '    $2 {', $content);

        if (!is_string($content)) {
            return;
        }

        // Place brace's class at the end of the line
        $content = preg_replace('/(class) (.*)\n{\n(.*)/', "$1 $2 {\n\n$3", $content);

        if (!is_string($content)) {
            return;
        }

        // Add line break before last end brace
        $content = preg_replace('/( {4})}\n}/', "$1}\n\n}", $content);

        file_put_contents($filename, $content);
    }
}
