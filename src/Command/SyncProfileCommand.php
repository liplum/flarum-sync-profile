<?php

namespace Liplum\SyncProfile\Command;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Flarum\Foundation\Config;
use Psr\Log\LoggerInterface;
use Flarum\Extension\ExtensionManager;
use Illuminate\Contracts\Events\Dispatcher;
use Liplum\SyncProfile\SyncUtils;

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

  public function handle()
  {
    if (!$this->extensions->isEnabled('liplum-sync-profile-core')) return;
    $syncUsersEndpoint = $this->settings->get('liplum-sync-profile.syncUsersEndpoint');
    if (!$syncUsersEndpoint) return;
    $authorization = $this->getSettings('liplum-sync-profile.authorizationHeader');
    $this->debugLog("Starting user profile syncing.");
    SyncUtils::syncAllUsers(
      syncUsersEndpoint: $syncUsersEndpoint,
      dispatcher: $this->dispatcher,
      authorization: $authorization,
      client: $this->client,
    );
  }

  protected function debugLog(string $message)
  {
    if ($this->config->inDebugMode()) {
      $logger = resolve(LoggerInterface::class);
      $logger->info($message);
    }
  }

  private function getSettings(string $key)
  {
    return $this->config->offsetGet($key) ?? $this->settings->get($key);
  }
}
