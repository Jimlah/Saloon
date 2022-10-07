<?php

namespace Sammyjo20\Saloon\Traits;

use ReflectionException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Managers\RequestManager;

trait ManagesPlugins
{
    /**
     * The loaded plugins.
     *
     * @var array
     */
    private array $plugins = [];

    /**
     * Load all the plugins.
     *
     * @throws ReflectionException
     * @throws SaloonInvalidConnectorException
     */
    private function loadPlugins(): void
    {
        // Check for the interfaces on the request class and see if we need to load
        // any options. E.g. if they have the "hasBody" interface, we need to add the
        // body to the request.

        $connectorTraits = ReflectionHelper::getTraitsRecursively($this->request->getConnector());
        $requestTraits = ReflectionHelper::getTraitsRecursively($this->request);

        $this->scanTraits($connectorTraits, 'connector')
            ->scanTraits($requestTraits, 'request');
    }

    /**
     * Scan through each of the traits and attempt to find the "boot" method.
     * If it exists, then we will run it.
     *
     * @param array $traits
     * @param string $type
     * @return ManagesPlugins|RequestManager
     */
    private function scanTraits(array $traits, string $type): self
    {
        if ($type !== 'connector' && $type !== 'request') {
            return $this;
        }

        foreach ($traits as $trait) {
            $pluginName = ReflectionHelper::classBaseName($trait);

            if (in_array($pluginName, $this->plugins, true)) {
                continue;
            }

            $bootName = 'boot' . $pluginName;

            if (method_exists($trait, $bootName) === false) {
                continue;
            }

            if ($type === 'connector' && method_exists($this->connector, $bootName)) {
                $this->bootConnectorPlugin($bootName);
            }

            if ($type === 'request' && method_exists($this->request, $bootName)) {
                $this->bootRequestPlugin($bootName);
            }

            $this->plugins[] = $pluginName . $type;
        }

        return $this;
    }

    /**
     * Run the method on the connector.
     *
     * @param string $methodName
     */
    private function bootConnectorPlugin(string $methodName): void
    {
        $this->connector->{$methodName}($this->request);
    }

    /**
     * Run the boot method on the request.
     *
     * @param string $methodName
     */
    private function bootRequestPlugin(string $methodName): void
    {
        $this->request->{$methodName}($this->request);
    }
}
