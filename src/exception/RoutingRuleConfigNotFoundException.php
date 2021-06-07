<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter\exception;

use Throwable;

final class RoutingRuleConfigNotFoundException extends KnotRouterModuleException
{
    /**
     * RoutingRuleConfigNotFoundException constructor.
     *
     * @param string $routing_rule_config_file
     * @param Throwable|null $prev
     */
    public function __construct( string $routing_rule_config_file, Throwable $prev = null )
    {
        parent::__construct("Routing rule config file not found: {$routing_rule_config_file}", $prev);
    }
}