<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotRouter;

use Throwable, Closure;

use KnotLib\Router\DispatcherInterface;
use KnotLib\Router\Router;
use KnotLib\Router\Builder\PhpArrayRouterBuilder;

use KnotLib\Kernel\EventStream\Channels;
use KnotLib\Kernel\EventStream\Events;
use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ComponentModule;
use KnotLib\Kernel\Pipeline\MiddlewareInterface;

use KnotPhp\Module\KnotRouter\Adapter\KnotKernelRouterAdapter;

abstract class KnotRouterModule extends ComponentModule
{
    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponents() : array
    {
        return [
            Components::EVENTSTREAM,
            Components::PIPELINE,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return Components::ROUTER;
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
            $router = new Router($this->getDispatcher($app));
            (new PhpArrayRouterBuilder($router, $this->getRoutingRule()))->build();

            $app->router(new KnotKernelRouterAdapter($router));

            $app->pipeline()->push($this->getRoutingMiddleware($app));

            // fire event
            $app->eventstream()->channel(Channels::SYSTEM)->push(Events::ROUTER_ATTACHED, $router);
        }
        catch(Throwable $e)
        {
            throw new ModuleInstallationException(self::class, $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get dispatcher
     *
     * @param ApplicationInterface $app
     *
     * @return callback|Closure|DispatcherInterface
     */
    public abstract function getDispatcher(ApplicationInterface $app);


    /**
     * Get routing rule
     *
     * @return array
     */
    public abstract function getRoutingRule() : array;

    /**
     * Get routing middleware
     *
     * @param ApplicationInterface $app
     *
     * @return MiddlewareInterface
     */
    public abstract function getRoutingMiddleware(ApplicationInterface $app) : MiddlewareInterface;
}