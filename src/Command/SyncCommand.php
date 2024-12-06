<?php

namespace Liplum\SyncProfile\Command;

use Carbon\Carbon;
use Liplum\SyncProfile\Models\AuthSyncEvent;
use Flarum\Extension\ExtensionManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Flarum\Foundation\Config;
use Psr\Log\LoggerInterface;

class SyncCommand extends Command
{
  /**
   * {@inheritdoc}
   */
  protected $signature = 'liplum:sync-profile:sync-all';

  /**
   * {@inheritdoc}
   */
  protected $description = 'Sync all user profiles.';

  private $settings;
  private $client;
  private $config;
  private $extensions;

  public function __construct(SettingsRepositoryInterface $settings, Client $client, Config $config, ExtensionManager $extensions)
  {
    parent::__construct();

    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
  }

  protected function syncMulti()
  {
    $syncUsersEndpoint = $this->settings->get('liplum-sync-profile.syncUsersEndpoint');
    if (!$syncUsersEndpoint) {
      return;
    }
    $this->debugLog("Starting user profile syncing.");
    $emails = User::query()->where('email', '<>', '')->get([
      "email"
    ]);
    $this->debugLog("Will syncing: " . $emails);
    $authorization = $this->getSettings('liplum-sync-profile.authorizationHeader');
    $response =  $this->client->post($syncUsersEndpoint, [
      'headers' => [
        'Authorization' => $authorization,
      ],
      'json' => [
        "data" => [
          'type' => 'users',
          'attributes' => [
            'emails' => $emails,
          ],
        ]
      ]
    ]);
    $body = json_decode($response->getBody()->getContents());
    $users = $body["data"];
    $this->debugLog("Sync result: " . $users);
    foreach ($users as $user) {
      $attributes = $user["attributes"];
      $this->addSync($attributes);
    }
  }

  public function addSync($attributes)
  {
    $this->debugLog($attributes["email"] . "" . $attributes);

    // $event = new AuthSyncEvent();
    // $event->email = $attributes["email"];
    // $event->attributes = $attributes;
    // $event->time = Carbon::now();
    // $event->save();
  }

  public function handle()
  {
    if (!$this->extensions->isEnabled('liplum-sync-profile-core')) {
      return;
    }
    $this->syncMulti();
  }

  protected function debugLog(string $message)
  {
    if ($this->config->inDebugMode()) {
      /**
       * @var $logger LoggerInterface
       */
      $logger = resolve(LoggerInterface::class);
      $logger->info($message);
    }
  }
}
