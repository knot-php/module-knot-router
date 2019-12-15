<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotRouter\Exception;

use Throwable;

final class RoutingRuleConfigNotFoundException extends KnotRouterModuleException implements KnotRouterModuleExceptionInterface
{
    /**
     * RoutingRuleConfigNotFoundException constructor.
     *
     * @param string $routing_rule_config_file
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct( string $routing_rule_config_file, int $code = 0, Throwable $prev = null )
    {
        parent::__construct("Routing rule config file not found: {$routing_rule_config_file}", $code, $prev);
    }
}