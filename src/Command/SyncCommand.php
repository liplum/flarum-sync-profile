<?php

namespace Liplum\SyncProfile\Command;

use Liplum\SyncProfile\Event\SyncProfileEvent;
use Flarum\Extension\ExtensionManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Flarum\Foundation\Config;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Bus\Dispatcher;

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

  public function __construct(
    SettingsRepositoryInterface $settings,
    Client $client,
    Config $config,
    ExtensionManager $extensions,
  ) {
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
    $userWithEmails = User::query()->where('email', '<>', '')->get([
      "email"
    ])->toArray();
    $emails = array_map(function ($a) {
      return $a["email"];
    }, $userWithEmails);
    $this->debugLog(gettype($emails));
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
    $this->debugLog("$email will sync soon.");
    /**
     * @var Dispatcher $bus
     */
    $bus = resolve(Dispatcher::class);
    $bus->dispatch($event);
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
