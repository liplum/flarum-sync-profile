<?php

namespace Liplum\SyncProfile\Listener;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Registered;
use GuzzleHttp\Client;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Foundation\Config;
use Flarum\Extension\ExtensionManager;
use Psr\Log\LoggerInterface;

class UserListener
{
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
    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
    $this->dispatcher = $dispatcher;
  }

  public function subscribe(Dispatcher  $events)
  {
    $events->listen(Registered::class, [$this, 'handleRegistered']);
  }

  public function handleDeleted(Registered $event)
  {
    if (!$this->extensions->isEnabled('liplum-sync-profile-core')) return;
    $email = $event->user->email;
    if (!$email) return;
    $syncUserEndpoint = $this->settings->get('liplum-sync-profile.syncUserEndpoint');
    if (!$syncUserEndpoint) return;
    $authorization = $this->getSettings('liplum-sync-profile.authorizationHeader');
    $this->debugLog("Starting user profile syncing.");
    syncUser(
      email: $email,
      syncUserEndpoint: $syncUserEndpoint,
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