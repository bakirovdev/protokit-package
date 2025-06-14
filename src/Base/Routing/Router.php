<?php

namespace Bakirov\Protokit\Base\Routing;

use Illuminate\Routing\Router as BaseRouter;
use Illuminate\Routing\PendingResourceRegistration;

class Router extends BaseRouter
{
    public function apiResource($name, $controller, array $options = []): PendingResourceRegistration
    {
        $only = [
            'index',
            'show',
            'store',
            'update',
            'delete',
            'restore',
        ];

        $this->resourceParameters([$name => 'model']);

        if (isset($options['except'])) {
            $only = array_diff($only, (array)$options['except']);
        }

        $options = array_merge(['only' => $only], $options);

        return $this->resource($name, $controller, $options);
    }
}
