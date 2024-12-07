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
    $token = Arr::get($request->getQueryParams(), 'token');
    $body = json_decode($request->getBody()->getContents(), true);

    // Validate the token
    $webhookToken = $this->getSettings("liplum-sync-profile.webhookToken");
    if (!$webhookToken) {
      // Bad Request
      return new Response(503);
    }
    if (!$token === $webhookToken) {
      // Unauthorized
      return new Response(401);
    }

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

  private function handleProfileChanged($body)
  {
    $email = $body['data']['email'];
    $attributes = $body['data']['attributes'];
    addSync($this->dispatcher, $email, $attributes);
    return new Response(200);
  }
}