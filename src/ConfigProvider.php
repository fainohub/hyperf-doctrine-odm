<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM;

use FainoHub\HyperfDoctrineODM\Commands\DoctrineHydratorCommand;
use FainoHub\HyperfDoctrineODM\Commands\DoctrinePersistentCollectionCommand;
use FainoHub\HyperfDoctrineODM\Commands\DoctrineProxyCommand;

/**
 * Class ConfigProvider
 * @package FainoHub\HyperfDoctrineODM
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DoctrineDocumentManager::class => DoctrineDocumentManagerFactory::class,
            ],
            'commands' => [
                DoctrineProxyCommand::class,
                DoctrineHydratorCommand::class,
                DoctrinePersistentCollectionCommand::class,
            ],
            'listeners' => [

            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for doctrine.',
                    'source' => __DIR__ . '/../publish/doctrine-odm.php',
                    'destination' => BASE_PATH . '/config/autoload/doctrine-odm.php',
                ],
            ],
        ];
    }
}
