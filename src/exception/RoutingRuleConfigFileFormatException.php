<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter\exception;

use Throwable;

final class RoutingRuleConfigFileFormatException extends KnotRouterModuleException
{
    /**
     * RoutingRuleConfigFileFormatException constructor.
     *
     * @param string $routing_rule_config_file
     * @param Throwable|null $prev
     */
    public function __construct( string $routing_rule_config_file, Throwable $prev = null )
    {
        parent::__construct("Routing rule config file is invalid: {$routing_rule_config_file}", $prev);
    }
}