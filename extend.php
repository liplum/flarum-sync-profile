<?php

namespace Liplum\SyncProfile;

use Liplum\SyncProfile\Command\SyncProfileCommand;
use Liplum\SyncProfile\Controller\SyncWebhookController;
use Liplum\SyncProfile\Listener\UserListener;

use Flarum\Extend;
use Illuminate\Console\Scheduling\Event;

return [
  (new Extend\Frontend('admin'))
    ->js(__DIR__ . '/js/dist/admin.js'),

  new Extend\Locales(__DIR__ . '/resources/locale'),

  (new Extend\Console())
    ->command(SyncProfileCommand::class)
    ->schedule(SyncProfileCommand::class, function (Event $event) {
      $event->hourly()
        ->withoutOverlapping();
    }),

  (new Extend\Routes('api'))
    ->post('/sync-profile/webhook/{token}', 'liplum-sync-profile.sync-webhook', SyncWebhookController::class),

  (new Extend\Event)
    ->subscribe(UserListener::class),
];
