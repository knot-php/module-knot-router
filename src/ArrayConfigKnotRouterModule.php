<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotRouter;

use Throwable;

use KnotLib\Kernel\EventStream\Channels;
use KnotLib\Kernel\EventStream\Events;
use KnotLib\Kernel\Module\ComponentTypes;
use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Router\DispatcherInterface;
use KnotLib\Router\Router;
use KnotLib\Router\Builder\PhpArrayRouterBuilder;

use KnotPhp\Module\KnotRouter\Adapter\KnotKernelRouterAdapter;

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