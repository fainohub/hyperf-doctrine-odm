<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Commands;

use Doctrine\ODM\MongoDB\ConfigurationException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Tools\Console\MetadataFilter;
use FainoHub\HyperfDoctrineODM\DoctrineDocumentManager;
use Hyperf\Command\Command as HyperfCommand;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class DoctrineProxyCommand
 * @package FainoHub\HyperfDoctrineODM\Commands
 */
class DoctrineProxyCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var string
     */
    protected $name = 'doctrine:generate-proxies';

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    /**
     *
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('Doctrine generate proxies');
    }

    /**
     * @return int|void
     * @throws ConfigurationException
     */
    public function handle()
    {
        $dm = $this->container->get(DoctrineDocumentManager::class);
        assert($dm instanceof DocumentManager);

        /** @var ClassMetadata[] $metadatas */
        $metadatas = array_filter(
            $dm->getMetadataFactory()->getAllMetadata(),
            static function (ClassMetadata $classMetadata): bool {
                return !$classMetadata->isEmbeddedDocument && !$classMetadata->isMappedSuperclass && !$classMetadata->isQueryResultDocument;
            }
        );
        $metadatas = MetadataFilter::filter($metadatas, []);
        $destPath = $dm->getConfiguration()->getProxyDir();

        if (!is_string($destPath)) {
            throw ConfigurationException::proxyDirMissing();
        }

        if (!is_dir($destPath)) {
            mkdir($destPath, 0775, true);
        }

        $destPath = realpath($destPath);
        assert($destPath !== false);

        if (!file_exists($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        }

        if (!is_writable($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (count($metadatas)) {
            foreach ($metadatas as $metadata) {
                $this->line(
                    sprintf('Processing document "<info>%s</info>"', $metadata->name)
                );
            }

            // Generating Proxies
            $dm->getProxyFactory()->generateProxyClasses($metadatas);

            // Outputting information message
            $this->line(sprintf('Proxy classes generated to "<info>%s</info>"', $destPath));
        } else {
            $this->line('No Metadata Classes to process.', 'info');
        }

        return 0;
    }
}
