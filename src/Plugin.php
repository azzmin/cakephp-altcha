<?php
declare(strict_types=1);

namespace Altcha;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Routing\RouteBuilder;

class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);
    }

    public function routes(RouteBuilder $routes): void
    {
        $routes->plugin('Altcha', ['path' => '/altcha'], function (RouteBuilder $builder) {
            $builder->connect('/challenge.json', ['controller' => 'Challenges', 'action' => 'challenge']);
            $builder->fallbacks();
        });
        parent::routes($routes);
    }
}
