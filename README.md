# Flarum Sync Profile

A [Flarum](http://flarum.org) extension to sync user profile(attributes) when authenticated by an external identity provider. This extension provides support for syncing:

- Avatar
- Groups
- Bio
- Masquerade Attributes

## Get Started

Installation:

```sh
composer require liplum/flarum-sync-profile-core
composer require liplum/flarum-sync-profile
```

Update:

```sh
composer update liplum/flarum-sync-profile-core
composer update liplum/flarum-sync-profile
```

You'll need to start the [Flarum Scheduler](https://docs.flarum.org/scheduler/).

```sh
* * * * * cd /path-to-your-project && php flarum schedule:run >> /dev/null 2>&1
```

## Sync

### Flarum Setup

First, set both sync-users-endpoint and sync-user-endpoint.

If the sync-users-endpoint is set, all user profiles will be synced hourly.

If the sync-user-endpoint is set, each user profile will be synced when they were registered.

The payload of the hook request is in [JSON:API](https://jsonapi.org/) which Flarum uses,
and the authentication can be checked via the `Authorization` header.

For security issue, you should set the Authorization header in the [config.php](https://docs.flarum.org/config/)
instead of barely display on extension settings page for anyone who has the extension management permission.

```php
<?php return array (
  'debug' => false,
  // other configurations...
  "liplum-sync-profile" => array(
    "authorizationHeader" => "Bearer your_access_token"
  ),
);
```

Here is something like the Flarum backend would request the hook.

For single user:

```js
fetch(syncUserEndpointUrl, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer your_access_token'
  },
  body: JSON.stringify({
    "data": {
      "type": "users",
      "attributes": {
        "email": "example@example.com"
      }
    }
  })
})
```

For multiple users:

```js
fetch(syncUsersEndpointUrl, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer your_access_token'
  },
  body: JSON.stringify({
    "data": {
      "type": "users",
      "attributes": {
        "emails": [
          "example1@example.com",
          "example2@example.com",
        ]
      }
    }
  })
})
```

And the backend should handle the sync request and respond a the user attributes in [JSON:API](https://jsonapi.org/):

For single user:

```json
{
  "data": {
    "type": "users",
    "attributes": {
      "email": "example@example.com",
      "nickname": "Example Name",
      "avatarUrl": "https://example.com/avatar",
      "bio": "Example bio."
    }
  }
}
```

For multiple users:

```json
{
  "data": [{
    "type": "users",
    "attributes": {
      "email": "example1@example.com",
      "nickname": "User 1",
      "avatarUrl": "https://example.com/avatar1",
      "bio": "Example bio of user 1."
    }
  },{
    "type": "users",
    "attributes": {
      "email": "example2@example.com",
      "nickname": "User 2",
      "avatarUrl": "https://example.com/avatar2",
      "bio": "Example bio of user 2."
    }
  }]
}
```

### Backend Setup

Taking the [express.js](https://expressjs.com/) backend server as an example, you can set up the following routes.

```ts
```

### Manually Sync

You can run the command to manually trigger sync all users.

```bash
php flarum liplum:sync-profile:all
```

### Webhook

Set the webhook token for authentication.
If it's left empty, the webhook won't work.

The webhook endpoint is `/api/sync-profile/webhook/{api}`.

A full qualified URL is `https://fourm.example.com/api/sync-profile/webhook/{api}`.

```json
{
 "event": "profile-changed",
 "data": {
  "email": "email@example.com",
  "attributes": {
   "nickname": "Test User",
   "avatarUrl": "https://example.com/avatarUrl",
   "bio": "My bio."
  }
 }
}
```

Taking the [express.js](https://expressjs.com/) backend server as an example, you can set up the following routes.

```ts
```
