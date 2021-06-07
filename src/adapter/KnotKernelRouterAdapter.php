<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter\adapter;

use knotlib\router\RouterInterface as CalgamoRouterInterface;
use knotlib\router\exception\RoutingRuleBindingException as CalgamoRoutingRuleBindingException;

use knotlib\kernel\router\RouterInterface as CalgamoKernelRouterInterface;
use knotlib\kernel\exception\RoutingException;

class KnotKernelRouterAdapter implements CalgamoKernelRouterInterface
{
    /** @var CalgamoRouterInterface */
    private $router;

    /**
     * CalgamoRouterAdapter constructor.
     *
     * @param CalgamoRouterInterface $router
     */
    public function __construct(CalgamoRouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Bind rule
     *
     * @param string $routing_rule
     * @param string $filter
     * @param string $route_name
     * @param callable $callback
     *
     * @return CalgamoKernelRouterInterface
     *
     * @throws RoutingException
     */
    public function bind(string $routing_rule, string $filter, string $route_name, callable $callback = null) : CalgamoKernelRouterInterface
    {
        try{
            $this->router->bind($routing_rule, $filter, $route_name, $callback);
        }
        catch(CalgamoRoutingRuleBindingException $e)
        {
            throw new RoutingException('Binding failed:' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Set not found callback
     *
     * @param callable $not_found_callback
     *
     * @return CalgamoKernelRouterInterface
     */
    public function notFound(callable $not_found_callback = null) : CalgamoKernelRouterInterface
    {
        $this->router->notFound($not_found_callback);
        return $this;
    }

    /**
     * Route path
     *
     * filter - '*' means all filter passes
     *
     * @param string $route_url
     * @param string $filter
     * @param callable $callback
     */
    public function route(string $route_url, string $filter, callable $callback = null)
    {
        $this->router->route($route_url, $filter, $callback);
    }
}