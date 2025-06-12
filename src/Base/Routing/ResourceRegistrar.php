<?php

namespace Bakirov\Protokit\Base\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Routing\Route;

class ResourceRegistrar extends BaseResourceRegistrar
{
    protected $resourceDefaults = [
        'index',
        'show',
        'store',
        'update',
        'delete',
        'restore',
    ];


    /**
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array $options
     * @return Route
     */
    protected function addResourceDelete($name, $base, $controller, $options): Route
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}';

        $action = $this->getResourceAction($name, $controller, 'delete', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array $options
     * @return Route
     */
    protected function addResourceRestore($name, $base, $controller, $options): Route
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{value}/restore';

        $action = $this->getResourceAction($name, $controller, 'restore', $options);

        return $this->router->delete($uri, $action);
    }

}
