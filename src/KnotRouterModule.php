<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotRouter;

use Throwable;

use KnotLib\Kernel\FileSystem\Dir;
use KnotLib\Kernel\FileSystem\FileSystemInterface;
use KnotLib\Kernel\EventStream\Channels;
use KnotLib\Kernel\EventStream\Events;
use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ComponentModule;

use KnotLib\Router\DispatcherInterface;
use KnotLib\Router\Router;
use KnotLib\Router\Builder\PhpArrayRouterBuilder;

use KnotPhp\Module\KnotRouter\Exception\RoutingRuleConfigFileFormatException;
use KnotPhp\Module\KnotRouter\Exception\RoutingRuleConfigNotFoundException;
use KnotPhp\Module\KnotRouter\Adapter\KnotKernelRouterAdapter;

final class KnotRouterModule extends ComponentModule
{
    const ROUTING_RULE_CONFIG_FILE = 'route.config.php';

    /** @var @var DispatcherInterface */
    private $dispatcher;

    /**
     * KnotRouterModule constructor.
     *
     * @param DispatcherInterface|null $dispatcher
     */
    public function __construct(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
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
            // get routing rule from config file
            $routing_rules = $this->getRoutingRule($app->filesystem());

            // create router
            $router = new Router($this->dispatcher);
            (new PhpArrayRouterBuilder($router, $routing_rules))->build();

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

    /**
     * Get routing rule
     *
     * @param FileSystemInterface $fs
     *
     * @return array
     *
     * @throws RoutingRuleConfigNotFoundException
     * @throws RoutingRuleConfigFileFormatException
     */
    private function getRoutingRule(FileSystemInterface $fs) : array
    {
        $routing_rule_config_file = $fs->getFile(Dir::CONFIG, self::ROUTING_RULE_CONFIG_FILE);
        if (!is_file($routing_rule_config_file)){
            throw new RoutingRuleConfigNotFoundException($routing_rule_config_file);
        }
        /** @noinspection PhpIncludeInspection */
        $ret = require($routing_rule_config_file);
        if (!is_array($ret)){
            throw new RoutingRuleConfigFileFormatException($routing_rule_config_file);
        }
        return $ret;
    }
}