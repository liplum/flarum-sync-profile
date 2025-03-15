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
use Liplum\SyncProfile\Event\SyncProfileEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SyncWebhookController implements RequestHandlerInterface
{

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
    $this->settings = $settings;
    $this->client = $client;
    $this->config = $config;
    $this->extensions = $extensions;
    $this->dispatcher = $dispatcher;
    $this->log = $log;
  }

  public function handle(ServerRequestInterface $request): ResponseInterface
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
    $body = $request->getParsedBody();

    $event = $body['event'];

    switch ($event) {
      case 'profile-changed':
        return $this->handleProfileChanged($body);
      default:
        return new Response(400); // Bad Request
    }
  }

  private function getSettings(string $key, $default = null)
  {
    return $this->config->offsetGet($key) ?? $this->settings->get($key, $default);
  }

  private function handleProfileChanged($body)
  {
    $email = $body['data']['email'];
    $attributes = $body['data']['attributes'];
    $event = new SyncProfileEvent($email, $attributes);
    $this->dispatcher->dispatch($event);
    $this->log->debug("Synced $email from webhook");
    return new Response(200);
  }
}
