<?php

namespace Liplum\SyncProfile;

use Liplum\SyncProfile\Command\SyncCommand;

use Flarum\Extend;
use Flarum\Foundation\Paths;
use Illuminate\Console\Scheduling\Event;

return [
  (new Extend\Frontend('admin'))
    ->js(__DIR__ . '/js/dist/admin.js'),

  new Extend\Locales(__DIR__ . '/resources/locale'),

  (new Extend\Console())
    ->command(SyncCommand::class)
    ->schedule(SyncCommand::class, function (Event $event) {
      $paths = resolve(Paths::class);

      $event->hourly()
        ->withoutOverlapping()
        ->appendOutputTo($paths->storage . '/logs/liplum-sync-profile.log');
    }),
];
