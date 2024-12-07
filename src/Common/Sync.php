<?php

namespace Liplum\SyncProfile\Common;

use Flarum\User\User;
use GuzzleHttp\Client;
use Liplum\SyncProfile\Event\SyncProfileEvent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

function addSync(Dispatcher $dispatcher, string $email, $attributes)
{
  $event = new SyncProfileEvent($email, $attributes);
  $dispatcher->dispatch($event);
}

function syncAllUsers(
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
    addSync($dispatcher, $email, $attributes);
  }
}

function syncUser(
  string $email,
  Dispatcher $dispatcher,
  string $syncUserEndpoint,
  string $authorization,
  Client $client,
) {
  $response =  $client->post($syncUserEndpoint, [
    'headers' => [
      'Authorization' => $authorization,
    ],
    'json' => [
      "data" => [
        'type' => 'users',
        'attributes' => [
          'email' => $email,
        ],
      ]
    ]
  ]);
  $body = json_decode($response->getBody()->getContents(), true);
  $user = Arr::get($body, "data", []);
  $attributes = $user["attributes"];
  $email = $attributes["email"];
  addSync($dispatcher,  $email, $attributes);
}
