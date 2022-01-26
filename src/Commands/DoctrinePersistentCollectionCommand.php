<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Commands;

use Doctrine\ODM\MongoDB\ConfigurationException;
use Doctrine\ODM\MongoDB\DocumentManager;
use FainoHub\HyperfDoctrineODM\DoctrineDocumentManager;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class DoctrinePersistentCollectionCommand
 * @package FainoHub\HyperfDoctrineODM\Commands
 */
class DoctrinePersistentCollectionCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var string
     */
    protected $name = 'doctrine:generate-persistent-collections';

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Doctrine generate persistent collections');
    }

    /**
     * @return int|void
     * @throws ConfigurationException
     */
    public function handle()
    {
        $dm = $this->container->get(DoctrineDocumentManager::class);
        assert($dm instanceof DocumentManager);

        $destPath = $dm->getConfiguration()->getPersistentCollectionDir();

        if (!is_string($destPath)) {
            throw ConfigurationException::persistentCollectionDirMissing();
        }

        if (!is_dir($destPath)) {
            mkdir($destPath, 0775, true);
        }

        $destPath = realpath($destPath);
        assert($destPath !== false);

        if (!file_exists($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Persistent Collection destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        }

        if (!is_writable($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Persistent Collection directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        $config = $this->container->get(ConfigInterface::class);
        $persistentCollections = $config->get('doctrine-odm.persistent_collections');

        if (count($persistentCollections)) {
            $generator = $dm->getConfiguration()->getPersistentCollectionGenerator();

            foreach ($persistentCollections as $persistentCollection) {
                // Generating Persistent Collections
                $this->line(sprintf('Processing document "<info>%s</info>"', $persistentCollection));
                $generator->generateClass($persistentCollection, $destPath);
            }

            // Outputting information message
            $this->line(sprintf('Persistent Collections classes generated to "<info>%s</info>"', $destPath));
        } else {
            $this->line('No Metadata Classes to process.', 'info');
        }

        return 0;
    }
}
