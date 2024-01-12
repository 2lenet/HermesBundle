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

## Add bundle configuration

Add the configuration to the `config/packages/lle_hermes.yaml` file of your project:

```yaml
lle_hermes:
    root_dir: '%kernel.project_dir%'
    app_secret: '%env(APP_SECRET)%'
    app_domain: domain.of.app
    bounce_host: host.bounce.com
    bounce_port: 110
    bounce_user: user@bounce.com
    bounce_password: pass
    recipient_error_retry: 3
```

Don't put any protocol in the app_domain.
