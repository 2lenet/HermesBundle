# Upgrade to 1.15

## Why

Up to 1.14, mail templates (`subject`, `senderName`, `html`, `text`) were compiled with the **application** Twig
environment. Anyone allowed to edit a template — or to compose a mail whose body is compiled — could read the
application globals (`{{ app.request.server.get('DATABASE_URL') }}`, `APP_SECRET`…), include template files and call
every Twig function of the project, including those querying the database. The template validator even executed the
template when it was saved.

1.15 renders mail templates with a dedicated, sandboxed environment. See
[Mail templating](docs/templating.md#allowed-twig-sandbox) and
[Configuration](docs/configuration.md#mail-templates-twig-environment).

## Before upgrading: list what your templates use

1.15 is a minor version: a project requiring `^1.x` receives it with a plain `composer update`. Run the check below
**before** updating Hermes on a project, and complete the allow-list in the same deployment if needed.

Run this read-only query on each production database and look for tags, filters and functions that are not in the
default list ([Mail templating](docs/templating.md#allowed-twig-sandbox)), and for any `app.`, `include`, `source`:

```sql
SELECT 'template' AS source, t.code AS ref, t.tenant_id,
       CONCAT_WS('\n', t.subject, t.sender_name, t.html, t.text) AS content
FROM lle_hermes_template t
WHERE CONCAT_WS('', t.subject, t.sender_name, t.html, t.text) LIKE '%{%'

UNION ALL

SELECT 'mail', MIN(m.id), MIN(m.tenant_id),
       MIN(CONCAT_WS('\n', m.subject, m.html, m.text))
FROM lle_hermes_mail m
LEFT JOIN lle_hermes_template t ON t.id = m.template_id
WHERE m.created_at >= NOW() - INTERVAL 6 MONTH
  AND CONCAT_WS('', m.subject, m.html, m.text) LIKE '%{%'
  AND (t.id IS NULL OR NOT (m.html <=> t.html AND m.subject <=> t.subject AND m.text <=> t.text))
GROUP BY MD5(CONCAT_WS('\n', m.subject, m.html, m.text));
```

The second part lists mails whose content differs from their template (mails composed or edited by hand). The result
may contain text typed by users: keep it local.

Once 1.15 is installed (on a copy of the production database, or right after deploying, before the sending workers
run), list exactly what the sandbox refuses:

```bash
bin/console lle:hermes:check-templates
```

It checks templates, template translations and mails that may still be sent (`draft`, `sending`, `error`,
`partially_sent`) without rendering them, prints the refused fields with the reason and exits with a failure code if
any. Fix them, or allow the construct, until it succeeds.

## Behaviour changes

- **Templates are sandboxed by default.** A construct outside the allowed list is refused when the template is saved,
  and a mail using it puts its recipients in `error` (`Template error: …`) instead of being sent. Allow what your
  templates legitimately need with `lle_hermes.mail_twig.sandbox.allowed_*`.
- **No application globals, no template files, no project extensions.** `app`, `include`, `source`, `embed`, `extends`
  and the functions/filters of your own Twig extensions are gone from mail templates. Routing (`url`, `path`),
  translation (`trans`) and Intl (`format_*`) are kept when the application has them. Add other extensions with
  `lle_hermes.mail_twig.extensions` (and allow their functions/filters); extensions using a runtime class are
  supported (the application runtime loader is kept).
- **Objects are no longer readable.** Mail data are stored as JSON, so this only concerns code passing objects to
  `MailTemplater` directly: pass arrays instead.
- **`MailBuilder`, `HermesExtension` and `TwigSyntaxValidator` receive `MailTwigEnvironment`.** If you decorate or
  extend them, inject `Lle\HermesBundle\Twig\MailTwigEnvironment` (service `lle_hermes.mail_twig`) rather than
  `Twig\Environment`.
- **Your own rendering of template strings must use `MailTwigEnvironment`.** A preview or a custom templater still
  built on the application `Twig\Environment` keeps the 1.x exposure.
- **`TwigSyntaxValidator` checks with the mail environment without rendering**: it no longer runs the template (no
  database query, no data needed). It now also applies to `subject` and `senderName`, and reports a forbidden
  construct with its own message (`validator.twig_syntax.forbidden`). Errors that only happen at rendering — an
  unknown route given to `url()`, for instance — are no longer reported when saving, but on the recipient when sending.
  Its constructor now requires `MailTwigEnvironment`.
- **Back office screens show template errors** (`Template error: …`) instead of failing: `lle_hermes_get_templater`
  returns a `MailPreviewTemplater`. Do not use it to render a mail you send.
- **`MailTemplater` with another environment is deprecated**: it still works (without sandbox) and triggers a
  deprecation; 2.0 will require `MailTwigEnvironment`.
- **Template errors are not retried** and no longer put the whole mail in `error`: only the failing recipient is.
- **`twig/twig` ^3.28 is required** (strict sandbox policy).

## Temporary escape hatch

```yaml
lle_hermes:
    mail_twig:
        sandbox:
            enabled: false
```

keeps the dedicated environment (still no globals nor file access) without the sandbox, while you fix templates. Do not
keep it.
