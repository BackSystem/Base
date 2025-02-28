<?php

namespace BackSystem\Base;

use BackSystem\Base\Controller\LocaleController;
use BackSystem\Base\Orm\Subscriber\DoctrineMetadataQuotingSubscriber;
use BackSystem\Base\Orm\Subscriber\TimestampSubscriber;
use BackSystem\Base\Queue\QueueInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class BaseBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(QueueInterface::class)->addTag('queue.injectable');
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->services()->get(LocaleController::class)->arg(2, $config['locale_redirection']);
        $container->services()->get(DoctrineMetadataQuotingSubscriber::class)->arg(0, $config['orm']['surround_metadata_names_with_quotes']);
        $container->services()->get(TimestampSubscriber::class)->arg(2, $config['orm']['enable_timestamp_hydrators']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('locale_redirection')->defaultValue('home_index')->end()
            ->arrayNode('orm')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('surround_metadata_names_with_quotes')->defaultFalse()->end()
                    ->scalarNode('enable_timestamp_hydrators')->defaultFalse()->end()
                ->end()
            ->end();
    }
}
