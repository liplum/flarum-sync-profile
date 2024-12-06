<?php

namespace Liplum\SyncProfile\Command;

use Liplum\SyncProfile\Event\SyncProfileEvent;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Flarum\Foundation\Config;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Flarum\Extension\ExtensionManager;
use Illuminate\Contracts\Events\Dispatcher;

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
  private $dispatcher;

  public function __construct(
    SettingsRepositoryInterface $settings,
    Client $client,
    Config $config,
    ExtensionManager $extensions,
    Dispatcher $dispatcher,
  ) {
    parent::__construct();

    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
    $this->dispatcher = $dispatcher;
  }

  protected function syncAllUsers()
  {
    $syncUsersEndpoint = $this->settings->get('liplum-sync-profile.syncUsersEndpoint');
    if (!$syncUsersEndpoint) return;
    $this->debugLog("Starting user profile syncing.");
    $userWithEmails = User::query()->where('email', '<>', '')->get([
      "email"
    ])->toArray();
    $emails = array_map(function ($a) {
      return $a["email"];
    }, $userWithEmails);
    $this->debugLog("Will syncing: " . join(", ", $emails));
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
    $body = json_decode($response->getBody()->getContents(), true);
    $users = Arr::get($body, "data", []);
    foreach ($users as $user) {
      $attributes = $user["attributes"];
      $this->addSync($attributes);
    }
  }

  public function addSync($attributes)
  {
    $email = $attributes["email"];
    $event = new SyncProfileEvent($email, $attributes);
    $this->dispatcher->dispatch($event);
  }

  public function handle()
  {
    if (!$this->extensions->isEnabled('liplum-sync-profile-core')) {
      return;
    }
    $this->syncAllUsers();
  }

  protected function debugLog(string $message)
  {
    if ($this->config->inDebugMode()) {
      /**
       * @var LoggerInterface
       */
      $logger = resolve(LoggerInterface::class);
      $logger->info($message);
    }
  }
  private function getSettings(string $key)
  {
    return $this->config->offsetGet($key) ?? $this->settings->get($key);
  }
}
