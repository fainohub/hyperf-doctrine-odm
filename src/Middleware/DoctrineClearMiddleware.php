<?php

declare(strict_types=1);

namespace FainoHub\HyperfDoctrineODM\Middleware;

use FainoHub\HyperfDoctrineODM\DoctrineDocumentManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DoctrineClearMiddleware
 * @package FainoHub\HyperfDoctrineODM\Middleware
 */
class DoctrineClearMiddleware implements MiddlewareInterface
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
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $this->doctrineDocumentManager->clear();

        return $response;
    }
}
