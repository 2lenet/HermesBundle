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

## Retry on send failure

When sending fails with a transport or RFC error, the recipient is put in `retry` status and the mail's `sendAtDate` is pushed back instead of the recipient being immediately marked as `error`. Delays are progressive: 1 minute for the first retry, 1 hour for the second, then 1 day for every subsequent attempt (capped). The maximum number of retries (default `3`) can be configured:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    recipient_max_retry: 3
```

Once the recipient has been retried this many times without success, it is set to the final `error` status.