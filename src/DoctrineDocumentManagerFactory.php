<?php

declare (strict_types=1);

namespace FainoHub\HyperfDoctrineODM;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Hyperf\Contract\ConfigInterface;
use MongoDB\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class DoctrineDocumentManagerFactory
 * @package App\Shared\Infrastructure\Persistence\Doctrine
 */
class DoctrineDocumentManagerFactory
{
    /**
     * @var array
     */
    private array $doctrineConfig;

    /**
     * @param ContainerInterface $container
     * @return DocumentManager
     * @throws MappingException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DocumentManager
    {
        $this->doctrineConfig = $container->get(ConfigInterface::class)->get('doctrine-odm');

        return DoctrineDocumentManager::create(
            $this->createConnection(),
            $this->createConfiguration()
        );
    }

    /**
     * @return Configuration
     * @throws MappingException
     */
    private function createConfiguration(): Configuration
    {
        $config = new Configuration();
        $config->setProxyDir($this->doctrineConfig['proxy']['path']);
        $config->setProxyNamespace($this->doctrineConfig['proxy']['namespace']);
        $config->setHydratorDir($this->doctrineConfig['hydrator']['path']);
        $config->setHydratorNamespace($this->doctrineConfig['hydrator']['namespace']);
        $config->setPersistentCollectionDir($this->doctrineConfig['collection']['path']);
        $config->setPersistentCollectionNamespace($this->doctrineConfig['collection']['namespace']);
        $config->setDefaultDB($this->doctrineConfig['connection']['database']);
        $config->setMetadataDriverImpl($this->createDriver());

        $config->setAutoGenerateHydratorClasses(Configuration::AUTOGENERATE_NEVER);
        $config->setAutoGeneratePersistentCollectionClasses(Configuration::AUTOGENERATE_NEVER);

        spl_autoload_register($config->getProxyManagerConfiguration()->getProxyAutoloader());

        $this->loadTypes();

        return $config;
    }

    /**
     * @return Client
     */
    private function createConnection(): Client
    {
        return new Client(
            $this->doctrineConfig['connection']['server'],
            [],
            ['typeMap' => DocumentManager::CLIENT_TYPEMAP]
        );
    }

    /**
     * @return MappingDriver
     */
    private function createDriver(): MappingDriver
    {
        switch ($this->doctrineConfig['mapping']['driver']) {
            case 'xml':
                return new XmlDriver($this->doctrineConfig['mapping']['paths'], $this->doctrineConfig['mapping']['extension']);
            case 'annotation':
            default:
                return AnnotationDriver::create($this->doctrineConfig['mapping']['paths']);
        }
    }

    /**
     * @throws MappingException
     */
    private function loadTypes(): void
    {
        foreach ($this->doctrineConfig['types'] as $name => $className) {
            if (!Type::hasType($name)) {
                Type::addType($name, $className);
            }
        }
    }
}
