<?php

namespace Liplum\SyncProfile;

use Flarum\Extend;
use Flarum\Foundation\Paths;
use Liplum\SyncProfile\Console\UpdateCommand;
use Illuminate\Console\Scheduling\Event;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Console())
        ->command(UpdateCommand::class)
        ->schedule(UpdateCommand::class, function (Event $event) {
            $paths = resolve(Paths::class);

            $event->hourly()
                ->withoutOverlapping()
                ->appendOutputTo($paths->storage.'/logs/liplum-sync-profile.log');
        }),
];
