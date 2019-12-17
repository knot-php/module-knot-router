<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotRouter;

use Throwable;

use KnotLib\Kernel\EventStream\Channels;
use KnotLib\Kernel\EventStream\Events;
use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ComponentModule;

use KnotLib\Router\DispatcherInterface;
use KnotLib\Router\Router;
use KnotLib\Router\Builder\PhpArrayRouterBuilder;

use KnotPhp\Module\KnotRouter\Adapter\KnotKernelRouterAdapter;

final class ArrayConfigKnotRouterModule extends ComponentModule
{
    /** @var @var DispatcherInterface */
    private $dispatcher;

    /** @var array */
    private $routing_rule;

    /**
     * KnotRouterModule constructor.
     *
     * @param DispatcherInterface|null $dispatcher
     * @param array $routing_rule
     */
    public function __construct(DispatcherInterface $dispatcher = null, array $routing_rule = null)
    {
        $this->dispatcher = $dispatcher;
        $this->routing_rule = $routing_rule ?? [];
    }

    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponents() : array
    {
        return [
            Components::EVENTSTREAM,
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
            // create router
            $router = new Router($this->dispatcher);
            (new PhpArrayRouterBuilder($router, $this->routing_rule))->build();

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