<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM;

use Doctrine\ODM\MongoDB\Types\ClosureToPHP;
use Doctrine\ODM\MongoDB\Types\Type as MongoDBType;

/**
 * Class Type
 * @package PicPay\P2P\Commons\Shared\Infrastructure\Persistence\Doctrine\Types
 */
abstract class Type extends MongoDBType
{
    use ClosureToPHP;

    /**
     * @return string
     */
    abstract public static function type(): string;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this::type();
    }
}
