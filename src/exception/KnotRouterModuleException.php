<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter\exception;

use Throwable;

use knotlib\exception\KnotPhpException;

class KnotRouterModuleException extends KnotPhpException implements KnotRouterModuleExceptionInterface
{
    /**
     * KnotRouterModuleException constructor.
     *
     * @param string $message
     * @param Throwable|null $prev
     */
    public function __construct( string $message, Throwable $prev = null )
    {
        parent::__construct($message, 0, $prev);
    }
}