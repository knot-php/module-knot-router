<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter;

use Throwable;

use knotlib\kernel\eventstream\Channels;
use knotlib\kernel\eventstream\Events;
use knotlib\kernel\module\ComponentTypes;
use knotlib\kernel\exception\ModuleInstallationException;
use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\kernel\module\ModuleInterface;
use knotlib\router\DispatcherInterface;
use knotlib\router\Router;
use knotlib\router\builder\PhpArrayRouterBuilder;

use knotphp\module\knotrouter\adapter\KnotKernelRouterAdapter;

final class ArrayConfigKnotRouterModule implements ModuleInterface
{
    /** @var @var DispatcherInterface */
    private $dispatcher;

    /** @var array */
    private $routing_rules;

    /**
     * KnotRouterModule constructor.
     *
     * @param DispatcherInterface|null $dispatcher
     * @param array $routing_rules
     */
    public function __construct(DispatcherInterface $dispatcher = null, array $routing_rules = null)
    {
        $this->dispatcher = $dispatcher;
        $this->routing_rules = $routing_rules ?? [];
    }

    /**
     * Declare dependency on another modules
     *
     * @return array
     */
    public static function requiredModules() : array
    {
        return [];
    }

    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponentTypes() : array
    {
        return [
            ComponentTypes::EVENTSTREAM,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return ComponentTypes::ROUTER;
    }

    /**
     * Install module
     *
     * @param ApplicationInterface $app
     *
     * @throws ModuleInstallationException
     */
    public function install(ApplicationInterface $app)
    {
        try{
            // create router
            $router = new Router($this->dispatcher);
            (new PhpArrayRouterBuilder($router, $this->routing_rules))->build();

            // set router
            $app->router(new KnotKernelRouterAdapter($router));

            // fire event
            $app->eventstream()->channel(Channels::SYSTEM)->push(Events::ROUTER_ATTACHED, $router);
        }
        catch(Throwable $e)
        {
            throw new ModuleInstallationException(self::class, $e->getMessage(), 0, $e);
        }
    }
}