# Installation without Symfony Flex

If your application doesn't use Symfony Flex, you must following the next steps:

## Enable the Bundle

You have to enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Lle\HermesBundle\LleHermesBundle::class => ['all' => true],
];
```

## Setup routing

Add the bundle route to the `config/routes.yaml` file of your project:

```yaml
# config/route.yaml

# ...
hermes:
    resource: '@LleHermesBundle/Resources/config/routes.xml'
    prefix: /hermes
```

You need to add these routes to the `config/package/security.yaml` file for tracking, statistics and unsubscribtion to work:

```yaml
# config/package/security.yaml

security:
    access_control:
        - { path: ^/hermes/mailOpened, roles: PUBLIC_ACCESS }
        - { path: ^/hermes/statistics, roles: PUBLIC_ACCESS }
        - { path: ^/hermes/unsubscribe, roles: PUBLIC_ACCESS }
```

## Add bundle configuration

Add the configuration to the `config/packages/lle_hermes.yaml` file of your project:

```yaml
lle_hermes:
    root_dir: '%kernel.project_dir%'
    app_secret: '%env(APP_SECRET)%'
    app_domain: '%env(LLE_HERMES_DOMAIN)%'
    bounce_host: '%env(LLE_HERMES_BOUNCE_HOST)%'
    bounce_port: '%env(LLE_HERMES_BOUNCE_PORT)%'
    bounce_user: '%env(LLE_HERMES_BOUNCE_USER)%'
    bounce_password: '%env(LLE_HERMES_BOUNCE_PASSWORD)%'
    recipient_error_retry: 3
```
To properly configure the .env variables, See : [Configuration](configuration.md "Environment variables").
