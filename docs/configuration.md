# Configuration

## Environment variables

You must configure somme environment variables in `.env`:

```dotenv
LLE_HERMES_DOMAIN=domain.of.app # Used for generating links (unsubscription, tracking, statistics)
LLE_HERMES_BOUNCE_HOST=host.bounce.com
LLE_HERMES_BOUNCE_PORT=110
LLE_HERMES_BOUNCE_USER=user@bounce.com
LLE_HERMES_BOUNCE_PASSWORD=pass
```
Don't put any protocol in the app_domain.

## Locales

You must configure locales for [Crudit](https://github.com/2lenet/CruditBundle) dependency. For that, add the following lines in `config/services.yaml`:

```yaml
# config/services.yaml
parameters:
    locales: ['en', 'fr']
    default_locale: 'en'
```

## Layout

By default, the bundle automatically add icons in the menu, you can remove them by adding this line in `config/packages/lle_hermes.yaml`:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    menu_icons: false
```

## File storage

### Images converted from base64

You can configure the path for the images converted from base64. By default, path is `/upload/images/`:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    upload_path: '/uploads/
```

### Attachments

You can configure the path for the attachments. By default, path is `/data/hermes/attachments/`:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    upload_path: '/data/attachments/
```

Make sure that those repositories exist and have the correct rights !

## Template entity

Regardless of the `translatable_mail` setting, the bundle exposes a single contract for the template entity: `Lle\HermesBundle\Contracts\TemplateInterface`. Doctrine resolves it to the correct concrete class at runtime.

Always use `TemplateInterface` when type-hinting in your application code — never import `Template` or `TranslatableTemplate` directly:

```php
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;

class MyService
{
    public function __construct(
        private TemplateRepositoryInterface $templateRepository,
    ) {}

    public function doSomething(TemplateInterface $template): void
    {
        // ...
    }
}
```

## Translatable templates

By default, the bundle uses [Gedmo Translatable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md) to make the `Template` translatable fields (`libelle`, `senderName`, `senderEmail`, `subject`, `html`, `mjml`, `text`) editable per locale. When enabled, the bundle automatically prepends the required `doctrine` (mapping for `Gedmo\Translatable\Entity`) and `stof_doctrine_extensions` (translatable listener) configurations.

If you don't want translatable templates, disable the feature:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    translatable_mail: false
```

When `translatable_mail` is `false`:
- `TemplateType` and `PersonalizedTemplateType` render their translatable fields as plain inputs instead of the `GedmoTranslatableType` widget.
- The bundle does **not** prepend the Gedmo / Stof translatable configuration — your project is free to leave them out entirely.

## Mail templates Twig environment

Mail templates are rendered by a dedicated, sandboxed Twig environment (service `lle_hermes.mail_twig`, autowirable as
`Lle\HermesBundle\Twig\MailTwigEnvironment`). See [Mail templating](templating.md "Allowed Twig (sandbox)") for what
templates may use by default.

It contains the Twig core, `HermesConsentExtension` and, when your application has them, the routing
(`twig.extension.routing`), translation (`twig.extension.trans`) and Intl (`twig.extension.intl`) extensions. To let
templates use a function or filter of your project, add its extension **and** allow the function or filter:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    mail_twig:
        extensions:
            - Lle\ConfigBundle\Twig\ConfigExtension   # service id of the Twig extension
        sandbox:
            allowed_functions: ['get_config_value']
            allowed_filters: []
            allowed_tags: []
```

Extensions declaring runtime callables (`new TwigFilter('foo', [FooRuntime::class, 'foo'])`) work as in the
application: the application runtime loader is kept.

To check the templates and the mails not sent yet against the current configuration (after adding or tightening an
allow-list, before deploying), run `bin/console lle:hermes:check-templates`.

The `allowed_*` lists are added to the bundle defaults, which are always kept. Only allow functions that cannot reveal
more than the template data: a function reading the database, the configuration or the environment is readable by
anyone who can edit a template.

Code of your project that renders a template string itself (preview, custom templater…) must use this environment, not
the application one:

```php
public function __construct(protected MailTwigEnvironment $mailTwig) {}

$templater = new MailTemplater($mail, $this->mailTwig, $this->router);
```

`sandbox.enabled: false` keeps the dedicated environment (no globals, no file access) without the sandbox. It is meant
as a temporary escape hatch while fixing existing templates, not as a setting to keep.

## Retention of recipients in error

When sending fails for an email address, an `EmailError` row is incremented. Past a threshold (default `3`), templates that do not allow `sendToErrors` will skip that recipient. You can change the threshold:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    recipient_error_retry: 5
```

## Multi-tenant

If your project is multi-tenant, declare the tenant entity class so Hermes can scope its entities. It must implement the project's tenant contract.

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    tenant_class: App\Entity\Tenant
```

See [multitenant.md](multitenant.md) for the full setup.

## Attachment lifetime

Mail attachments are kept for `365` days by default and then purged by the `lle:hermes:delete-attachments` command. Tune this for your retention policy:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    attachment_nb_days_before_deletion: 90
```

## Full reference

All keys live under the `lle_hermes` root in `config/packages/lle_hermes.yaml`:

| Key | Type | Required | Default | Purpose |
| --- | --- | --- | --- | --- |
| `root_dir` | string | yes | — | Project root directory (typically `%kernel.project_dir%`). |
| `app_secret` | string | yes | — | Secret used to sign unsubscribe and tracking links. |
| `app_domain` | string | yes | — | Public domain used to build links. No protocol. |
| `bounce_host` | string | yes | — | POP/IMAP host that collects bounces. |
| `bounce_port` | string | yes | — | Port for the bounce mailbox. |
| `bounce_user` | string | yes | — | Username for the bounce mailbox. |
| `bounce_password` | string | yes | — | Password for the bounce mailbox. |
| `attachment_path` | string | no | `/data/hermes/attachments/` | Filesystem path where attachments are stored. |
| `upload_path` | string | no | `/upload/images/` | Path where images decoded from base64 are written. |
| `menu_icons` | bool | no | `true` | Show icons in the Crudit menu entries. |
| `recipient_error_retry` | int | no | `3` | Max `EmailError.nbError` value before a recipient is skipped by templates with `sendToErrors=false`. |
| `tenant_class` | string\|null | no | `null` | FQCN of the tenant entity when multi-tenant is enabled. |
| `attachment_nb_days_before_deletion` | int | no | `365` | Retention of mail attachments before cleanup. |
| `translatable_mail` | bool | no | `true` | Enable Gedmo translatable fields on `Template`. See above. |
| `mail_twig.extensions` | string[] | no | `[]` | Service ids of project Twig extensions added to the mail environment. |
| `mail_twig.sandbox.enabled` | bool | no | `true` | Sandbox mail templates. See [Mail templates Twig environment](#mail-templates-twig-environment). |
| `mail_twig.sandbox.allowed_tags` | string[] | no | `[]` | Tags allowed on top of the defaults. |
| `mail_twig.sandbox.allowed_filters` | string[] | no | `[]` | Filters allowed on top of the defaults. |
| `mail_twig.sandbox.allowed_functions` | string[] | no | `[]` | Functions allowed on top of the defaults. |

## Retry on send failure

When sending fails with a transport error, the recipient is put in `retry` status and the mail's `sendAtDate` is pushed back instead of the recipient being immediately marked as `error`. Delays are progressive: 1 minute for the first retry, 1 hour for the second, then 1 day for every subsequent attempt (capped). The maximum number of retries (default `3`) can be configured:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    recipient_max_retry: 3
```

An address with an invalid format (`RfcComplianceException`) is not retried: the recipient is marked as `error` immediately.

A template error (construct refused by the sandbox, invalid Twig, rendering failing on the recipient data) is not
retried either: the recipient is marked as `error` with `Template error: <reason>` as error message, and the other
recipients of the mail are still sent.

Once the recipient has been retried this many times without success, it is set to the final `error` status.
