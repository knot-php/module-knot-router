<?php
declare(strict_types=1);

namespace knotphp\module\knotrouter;

use Throwable;

use knotlib\kernel\filesystem\Dir;
use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\eventstream\Channels;
use knotlib\kernel\eventstream\Events;
use knotlib\kernel\module\ComponentTypes;
use knotlib\kernel\exception\ModuleInstallationException;
use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\router\DispatcherInterface;
use knotlib\router\Router;
use knotlib\router\Builder\PhpArrayRouterBuilder;
use knotlib\kernel\module\ModuleInterface;

use knotphp\module\knotrouter\exception\RoutingRuleConfigFileFormatException;
use knotphp\module\knotrouter\exception\RoutingRuleConfigNotFoundException;
use knotphp\module\knotrouter\adapter\KnotKernelRouterAdapter;

final class FileConfigKnotRouterModule implements ModuleInterface
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