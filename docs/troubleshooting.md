# Common errors

## Why my mail is not sent ?

1. Did you create the mail with the `sending` status (default parameter of create() method) ?

```php
$this->mailer->create($mail);
```

See : [Usage](usage.md "Send mail").

2. Did you run the command to send the mails in queue ?

```bash
bin/console lle:hermes:send
```

See : [Commands](commands.md "Send mails in queue").

2. Do you have a lot a mails in queue ?

If you have a cron, just wait for your mail to be sent. 
You can increase the numbers of mails per batch with `--nb=`

```bash
bin/console lle:hermes:send --nb=1000
```

See : [Commands](commands.md "Send mails in queue").

## Where can I find details about a sending failure ?

Hermes logs every sending (success, retry, error, skipped recipient) on the `hermes` Monolog channel.
The context contains the recipient, the mail, the masked DSN, the exception and the SMTP dialog with the server.

To write them in a dedicated file, add a handler in `config/packages/monolog.yaml` :

```yaml
monolog:
    channels: ['hermes']
    handlers:
        hermes:
            type: rotating_file
            path: '%kernel.logs_dir%/hermes.log'
            level: info # use "warning" to keep only retries and errors
            max_files: 30
            channels: ['hermes']
```

Remember to exclude this channel from your other handlers (`channels: ['!hermes']`) if you don't want the logs twice.
