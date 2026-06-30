# Installation

## Installation

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Execute the following command to add the bundle to the dependencies of your project:

```bash
composer require 2lenet/hermes-bundle
```

If your application doesn't use Symfony Flex, refer to this following instructions : [Installation steps without flex](no-flex.md).

## Database schema

You need to update your database schema to complete the installation:

```bash
bin/console make:migration
bin/console doctrine:migrations:migrate
```

## Compile assets

```bash
bin/console assets:install
npm run build
```

## Cron

If you want your emails to be sent automatically, you must add a cron to run the send command:

```cronexp
* * * * * root cd /var/www/html/ && php bin/console lle:hermes:send
```

If you want errors to be recovered automatically, you must add a cron to run the command:
```cronexp
* * * * * root cd /var/www/html/ && php bin/console lle:hermes:recover-errors
```

## Permissions

HermesBundle depends on [Crudit Bundle](https://github.com/2lenet/CruditBundle), if you use it, make sure that each screen and action have their own role.
Make sure you've set the correct permissions in the [Crudit](https://github.com/2lenet/CruditBundle) backend for this bundle!

## Additional configuration

To configure the bundle, refer to this following instructions : [Configuration](configuration.md).

## Upgrading from a version without `TranslatableTemplate`

If you were using the `Template` entity directly in your application (type-hints, `instanceof` checks, Doctrine queries), you must replace those references with `TemplateInterface`:

```php
// Before
use Lle\HermesBundle\Entity\Template;

public function myMethod(Template $template): void {}

// After
use Lle\HermesBundle\Contracts\TemplateInterface;

public function myMethod(TemplateInterface $template): void {}
```

No database migration is required: both modes use the same `lle_hermes_template` table.

