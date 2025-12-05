<?php

namespace TransformStudios\Front\UpdateScripts;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class AddToLoggingConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return App::isLocal();
    }

    public function update()
    {
        if (Config::has('logging.channels.front')) {
            return;
        }

        ConfigWriter::edit('logging')
            ->set('channels.front', [
                'driver' => 'custom',
                'via' => \TransformStudios\Front\Logging\Logger::class,
                'level' => ConfigWriter::f()->env('FRONT_LOGGING_LEVEL', 'error'),
            ])->save();
        dd('saved');
    }
}
