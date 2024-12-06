# Flarum Sync Profile

A [Flarum](http://flarum.org) extension to sync user profile(attributes) when authenticated by an external identity provider. This extension provides support for syncing:

- Avatar
- Groups
- Bio
- Masquerade Attributes

Some authentication protocols, such as SAML2, LDAP, OpenID Connect, etc have the ability to send attributes along with an authentication response. This extension provides a framework for syncing user attributes and permissions via that attribute response.

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