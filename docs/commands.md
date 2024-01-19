# Commands

## Send mails in queue

The send command has to be used to send the queued mails.

```bash
bin/console lle:hermes:send
```

The default numbers of mails by batch is set to 10.
To increase the number of mails by batch, you can use the `--nb` option :

```bash
bin/console lle:hermes:send --nb=100
```

## Recover email in error

This command has to be used to retrieve emails in error and add them to database.
The bounce must be configured if this command is executed.

```bash
bin/console lle:hermes:recover-errors
```

The default numbers of mails by batch is set to 50.
To increase the number of mails by batch, you can use the `--nb` option :

```bash
bin/console lle:hermes:recover-errors --nb=100
```
