# Errors management

This bundle includes an error management.

## Principle

If a mail can't be delivered into a mailbox for some reason, this mail is sent back to the bounce server in the `bounce_user` mailbox.
A command will recover mails in this mailbox, check that it's an error email, and save it in database.

When a mail is sent, if the template defines it, the mail will not be sent to the recipient if the email address has already 3 errors. This number of retries can be configured.

## Configuration

Add the configuration to the `config/packages/lle_hermes.yaml` file of your project:

```yaml
lle_hermes:
    bounce_host: '%env(LLE_HERMES_BOUNCE_HOST)%'
    bounce_port: '%env(LLE_HERMES_BOUNCE_PORT)%'
    bounce_user: '%env(LLE_HERMES_BOUNCE_USER)%'
    bounce_password: '%env(LLE_HERMES_BOUNCE_PASSWORD)%'
    recipient_error_retry: 3
```
To properly configure the .env variables, See : [Configuration](configuration.md "Environment variables").

You need to have **php-imap** on your project for Hermes to work.

## Ignore errors

If you want to send a mail to a recipient who has already exceeded the maximum number of errors, you can activate option "Send to email addresses in error" in the template options:
![Templates activate errors](./img/template-activate-errors.png)
