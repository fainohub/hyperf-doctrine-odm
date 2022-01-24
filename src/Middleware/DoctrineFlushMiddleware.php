<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Middleware;

use Doctrine\ODM\MongoDB\MongoDBException;
use FainoHub\HyperfDoctrineODM\DoctrineDocumentManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DoctrineFlushMiddleware
 * @package FainoHub\HyperfDoctrineODM\Middleware
 */
class DoctrineFlushMiddleware implements MiddlewareInterface
{
    /**
     * @var DoctrineDocumentManager
     */
    private DoctrineDocumentManager $doctrineDocumentManager;

    /**
     * DoctrineDocumentManager constructor.
     * @param DoctrineDocumentManager $doctrineDocumentManager
     */
    public function __construct(DoctrineDocumentManager $doctrineDocumentManager)
    {
        $this->doctrineDocumentManager = $doctrineDocumentManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws MongoDBException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $this->doctrineDocumentManager->flush();

        return $response;
    }
}
