<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Aspect;

use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use MongoDB\Collection;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Throwable;
use const OpenTracing\Tags\SPAN_KIND_RPC_CLIENT;

/**
 * Class MongoTracerAspect
 * @package FainoHub\HyperfDoctrineODM\Aspect
 */
class MongoTracerAspect implements AroundInterface
{
    use SpanStarter;

    public array $classes = [
        Collection::class . '::aggregate*',
        Collection::class . '::count*',
        Collection::class . '::create*',
        Collection::class . '::delete*',
        Collection::class . '::drop*',
        Collection::class . '::find*',
        Collection::class . '::insert*',
        Collection::class . '::replace*',
        Collection::class . '::update*',
    ];

    public array $annotations = [];

    /**
     * @var Tracer
     */
    private Tracer $tracer;

    /**
     * GuzzleClientAspect constructor.
     * @param Tracer $tracer
     */
    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     * @throws Exception
     * @throws Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /**
         * @var Collection $collection
         */
        $collection = $proceedingJoinPoint->getInstance();

        $collectionName = $collection->getCollectionName();
        $databaseName = $collection->getDatabaseName();

        $operation = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments;

        $filter = $arguments['keys']['filter'] ?? null;
        $options = $arguments['keys']['options'] ?? null;

        $key = "MongoDB {$collectionName} {$operation}";

        $span = $this->createSpan($key);

        $operationFilter = empty($filter) ? null : implode(', ', array_keys($filter));
        $operationOptions = empty($filter) ? null : implode(', ', array_keys($options));

        $span->setTag('db.system', 'mongodb');
        $span->setTag('db.name', $databaseName);
        $span->setTag('db.operation', $operation);
        $span->setTag('db.mongodb.collection', $collectionName);
        $span->setTag('db.operation.filter', $operationFilter);
        $span->setTag('db.operation.options', $operationOptions);

        try {
            $result = $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            $span->setTag('error', true);
            $span->setTag('error.msg', $e->getMessage());
            $span->setTag('error.code', $e->getCode());
            $span->setTag('error.stack', $e->getTraceAsString());

            throw $e;
        } finally {
            $span->finish();
        }

        return $result;
    }

    /**
     * @param string $name
     * @return Span
     */
    public function createSpan(string $name): Span
    {
        return $this->startSpan($name, [], SPAN_KIND_RPC_CLIENT);
    }
}
