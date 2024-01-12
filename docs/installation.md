# Installation

## Installation

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Execute the following command to add the bundle to the dependencies of your project:

```bash
composer require 2lenet/hermes-bundle
```

If your application doesn't use Symfony Flex, refer to this following instructions : [Installations steps without flex](no-flex.md).

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

If you want errors to be recover automatically, you must add a cron to run the command:
```cronexp
* * * * * root cd /var/www/html/ && php bin/console lle:hermes:recover-errors
```

## Permissions

Each screen and action have it's own role.
Make sure you've set the correct permissions in the [Crudit](https://github.com/2lenet/CruditBundle) backend for this bundle!

## Additional configuration

To configure the bundle, refer to this following instructions : [Configuration](configuration.md).

