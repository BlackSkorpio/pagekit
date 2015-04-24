<?php

namespace Pagekit\Hello;

use Pagekit\Application as App;
use Pagekit\Hello\Event\HelloListener;
use Pagekit\System\Extension;

class HelloExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function main(App $app)
    {
        $app->subscribe(new HelloListener());

        $app->on('widget.types', function($event, $widgets) {
            $widgets->registerType(new HelloWidget('site'));
            $widgets->registerType(new HelloWidget('dashboard'));
        });

        // dispatch event (check Hello\Event\HelloListener to see how subscribers work)
        $app->trigger('hello.boot');

        if (!$app['config']->get($this->name)) {
            $app['config']->set($this->name, [], true);
        }
    }

    public function enable()
    {
        $config = App::config($this->name);

        // run all migrations that are newer than the current version
        if ($version = App::migrator()->create('extensions/hello/migrations', $config->get('version'))->run()) {
            $config->set('version', $version);
        }
    }

    public function disable()
    {
        // do nothing
    }

    public function uninstall()
    {
        // drop all own tables (created in migrations)
        $util = App::db()->getUtility();
        $util->dropTable('@hello_greetings');

        // remove the config
        App::config()->remove($this->name);
    }
}
