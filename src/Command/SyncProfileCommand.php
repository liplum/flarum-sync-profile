<?php

namespace Liplum\SyncProfile\Command;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Flarum\Foundation\Config;
use Psr\Log\LoggerInterface;
use Flarum\Extension\ExtensionManager;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Liplum\SyncProfile\Event\SyncProfileEvent;

class SyncProfileCommand extends Command
{
  /**
   * {@inheritdoc}
   */
  protected $signature = 'liplum:sync-profile:all';

  /**
   * {@inheritdoc}
   */
  protected $description = 'Sync all user profiles.';

  private $settings;
  private $client;
  private $config;
  private $extensions;
  private $dispatcher;
  private $log;

  public function __construct(
    SettingsRepositoryInterface $settings,
    Client $client,
    Config $config,
    ExtensionManager $extensions,
    Dispatcher $dispatcher,
    LoggerInterface $log,
  ) {
    parent::__construct();

    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
    $this->dispatcher = $dispatcher;
    $this->log = $log;
  }

  public function handle()
  {
    if (!$this->extensions->isEnabled('liplum-sync-profile-core')) return;
    $syncUsersEndpoint = $this->settings->get('liplum-sync-profile.syncUsersEndpoint');
    if (!$syncUsersEndpoint) return;
    $authorization = $this->getSettings('liplum-sync-profile.authorizationHeader');
    $this->log->debug("Starting user profile syncing.");
    static::syncAllUsers(
      syncUsersEndpoint: $syncUsersEndpoint,
      dispatcher: $this->dispatcher,
      authorization: $authorization,
      client: $this->client,
    );
  }

  public static function syncAllUsers(
    Dispatcher $dispatcher,
    string $syncUsersEndpoint,
    string $authorization,
    Client $client,
  ) {
    $userWithEmails = User::query()->where('email', '<>', '')->get([
      "email"
    ])->toArray();
    $emails = array_map(function ($a) {
      return $a["email"];
    }, $userWithEmails);
    $response =  $client->post($syncUsersEndpoint, [
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
    $body = json_decode($response->getBody()->getContents(), true);
    $users = Arr::get($body, "data", []);
    foreach ($users as $user) {
      $attributes = $user["attributes"];
      $email = $attributes["email"];
      $event = new SyncProfileEvent($email, $attributes);
      $dispatcher->dispatch($event);
    }
  }

  private function getSettings(string $key)
  {
    return $this->config->offsetGet($key) ?? $this->settings->get($key);
  }
}
