# HermesBundle

[![Build Status](https://github.com/2lenet/HermesBundle/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/2lenet/HermesBundle/actions)
[![Build Status](https://github.com/2lenet/HermesBundle/actions/workflows/validate.yml/badge.svg?branch=main)](https://github.com/2lenet/HermesBundle/actions)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute:

```console
$ composer require 2lenet/hermes-bundle
```

### Step 2: Configure the Bundle

Add a file in /config/packages/hermes.yaml for the configuration :

:warning: Don't put any protocol in the `app_domain`

```yaml 
lle_hermes:
  root_dir: /var/www/html
  app_secret: toto
  app_domain: titi
  bounce_email: bounce@kiwi-backup.com
  bounce_host: mail.2le.net
  bounce_pass: toto
```

The bounce email is the adress where error emails will be sent

You can configure if you want or not icons in the menu :

```yaml 
lle_hermes:
    menu_icons: false
```

You can configure the path for the images converted from base64. By default, path is `/upload/images/`:

```yaml 
lle_hermes:
    upload_path: '/uploads/
```

Make sure that this repository exists.

### Step 3: Configure locales

You must configure locale for Crudit dependency. For that, add the folloxing lines in `config/services.yaml`:

```yaml
parameters:
    locales: ['xx', 'yy']
    default_locale: 'xx'
```

### Step 4: Add cron

:warning: Don't forget to add the cron if you want your emails to be sent.

`* * * * * root cd /var/www/html/  && php bin/console lle:hermes:send`

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable
version of this bundle:

```console
$ composer require 2lenet/hermes-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Lle\HermesBundle\LleHermesBundle::class => ['all' => true],
];
```

### Step 3: Add Route

Then, add the bundle route to the `config/routes.yaml` file of your project:

```yaml
# config/route.yaml

# ...
hermes:
    resource: "@LleHermesBundle/Resources/config/routes.xml"
    prefix: /hermes
```

### Step 4: Add cron

:warning: Don't forget to add the cron if you want your emails to be sent.

`* * * * * root cd /var/www/html/  && php bin/console lle:hermes:send`

## Notes

### Messenger

If you have Messenger installed and use default configuration, the mails will be asynchronous and sent in a queue. You
either need to uninstall Messenger (DoctrineMessenger is installed by default on Symfony projects) or configure
Hermès/Messenger differently.

## Multi tenant
### Configuration
You can enable the multi tenant mode by adding the FCQN in `tenant_class` line in the configuration file.
In the following exemple we need to separate emails by Establishment :
```yaml
# config/pakage/lle_hermes.yaml
lle_hermes:
...
tenant_class: App\Entity\Establishment

In your user entity add the `MultiTenantInterface` with the method `getTenantId` :

```php
<?php

namespace App\Entity;

...
use Lle\HermesBundle\Contracts\MultiTenantInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements MultiTenantInterface
{

...

    public function getTenantId(): int
    {
        return $this->getCurrentEstablishment()->getId();
    }
}

```

Now the dashboard, mail and recipient sections are filtred by establishment.
