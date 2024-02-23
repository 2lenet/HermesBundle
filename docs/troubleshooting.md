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
