<?php

namespace Liplum\SyncProfile\Controller;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\Config;
use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\Events\Dispatcher;
use Psr\Log\LoggerInterface;

class SyncWebhookController extends RequestHandlerInterface
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
    parent::__construct();

    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
    $this->dispatcher = $dispatcher;
  }

  public function handle(ServerRequestInterface $request)
  {
    $webhookToken = $this->getSettings("liplum-sync-profile.webhookToken");
    if (!$webhookToken) {
      // Bad Request
      return new Response(503);
    }
    $token = Arr::get($request->getQueryParams(), 'token');
    // Validate the token
    if (!$token === $webhookToken) {
      // Unauthorized
      return new Response(401);
    }

    $body = json_decode($request->getBody()->getContents(), true);

    $event = $body['event'];

    switch ($event) {
      case 'profile-changed':
        return $this->handleProfileChanged($body);
      default:
        return new Response(400); // Bad Request
    }
  }

  private function getSettings(string $key)
  {
    return $this->config->offsetGet($key) ?? $this->settings->get($key);
  }

  protected function debugLog(string $message)
  {
    if ($this->config->inDebugMode()) {
      $logger = resolve(LoggerInterface::class);
      $logger->info($message);
    }
  }

  private function handleProfileChanged($body)
  {
    $email = $body['data']['email'];
    $attributes = $body['data']['attributes'];
    addSync($this->dispatcher, $email, $attributes);
    $this->debugLog("Synced $email from webhook");
    return new Response(200);
  }
}
