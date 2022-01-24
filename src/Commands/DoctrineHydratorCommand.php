<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Commands;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Tools\Console\MetadataFilter;
use FainoHub\HyperfDoctrineODM\DoctrineDocumentManager;
use Hyperf\Command\Command as HyperfCommand;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class DoctrineHydratorCommand
 * @package FainoHub\HyperfDoctrineODM\Commands
 */
class DoctrineHydratorCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var string
     */
    protected $name = 'doctrine:generate-hydrators';

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
        $this->setDescription('Doctrine generate hydrators');
    }

    /**
     * @return int|void
     */
    public function handle()
    {
        $dm = $this->container->get(DoctrineDocumentManager::class);
        assert($dm instanceof DocumentManager);

        $metadatas = $dm->getMetadataFactory()->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, []);
        $destPath = $dm->getConfiguration()->getHydratorDir();

        if (!is_dir($destPath)) {
            mkdir($destPath, 0775, true);
        }

        $destPath = realpath($destPath);
        assert($destPath !== false);

        if (!file_exists($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Hydrators destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        }

        if (!is_writable($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Hydrators destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (count($metadatas)) {
            foreach ($metadatas as $metadata) {
                $this->line(
                    sprintf('Processing document "<info>%s</info>"', $metadata->name)
                );
            }

            // Generating Hydrators
            $dm->getHydratorFactory()->generateHydratorClasses($metadatas, $destPath);

            // Outputting information message
            $this->line(sprintf('Hydrator classes generated to "<info>%s</info>"', $destPath));
        } else {
            $this->line('No Metadata Classes to process.', 'info');
        }

        return 0;
    }
}
