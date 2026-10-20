# Unsubscriptions

When the **Allow unsubscriptions** option (`unsubscriptions`) is enabled on a template, the `UNSUBSCRIBE_LINK` variable
is available in the template and recipients who unsubscribed are not sent the mail (their status becomes
`unsubscribed`).

```twig
<a href="{{ UNSUBSCRIBE_LINK }}">Unsubscribe</a>
```

## Unsubscribe from every email

As long as no template type exists, the unsubscribe page asks the recipient to confirm, then unsubscribes the address
from **every** template that allows unsubscriptions.

## Unsubscribe by template type

You can let recipients choose which kinds of emails they keep receiving (e.g. *Technical* / *Commercial*).

1. Create template types in the **Template types** screen (route `lle_hermes_crudit_typetemplate_index`, roles
   `ROLE_HERMES_TYPETEMPLATE_*`):
    - **Label**: displayed on the unsubscribe page,
    - **Code**: unique identifier, added to the unsubscribe link (`?type=<code>`),
    - **Unsubscription allowed**: whether the type is listed on the unsubscribe page.
2. Set the **Template type** of your templates (optional). The **Allow unsubscriptions** option is still required for
   the link to be generated and unsubscriptions to be taken into account.

### Unsubscribe page

As soon as at least one template type exists, the unsubscribe page shows:

- one checkbox per type with unsubscription allowed: checked types are the ones the recipient keeps receiving,
  unchecked types are unsubscribed. The type of the received email is unchecked by default;
- a **Stop receiving any email** checkbox: it creates an unsubscription without type, which blocks every email. While it
  is checked, the type checkboxes are disabled and ignored; the per-type choices are kept and apply again if it is
  unchecked later. It is checked when the address is already unsubscribed from every email.

### Sending

A mail is not sent to a recipient when:

- the address is unsubscribed from every email (**Stop receiving any email**, or an unsubscription made before types
  were created),
- or the address is unsubscribed from the type of the mail's template.

Templates without type, and templates whose type does not allow unsubscription, are therefore only blocked by
**Stop receiving any email**.

### Administration

- The **Unsubscribed** list shows the type of each unsubscription ("All types" when there is none) and can be filtered
  by type.
- A type used by templates cannot be deleted. Deleting a type also deletes the unsubscriptions from this type.

> Add the `menu.lle_hermes_typetemplate` key to your application's translations for the menu label, like the other
> Hermes menu entries.

## Migration

This feature adds the `lle_hermes_type_template` table and `type_template_id` columns on `lle_hermes_template` and
`lle_hermes_unsubscribe_email`. Generate and run a migration after updating the bundle:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

Existing unsubscriptions have no type and keep unsubscribing the address from every email.

## Customize the pages

The unsubscribe pages extend your `base.html.twig`. You can override them in
`templates/bundles/LleHermesBundle/unsubscribe/`:

- `index.html.twig`, `confirm.html.twig`, `cancel.html.twig`, `already_unsubscribe.html.twig`: unsubscription from
  every email, when no template type exists,
- `types.html.twig` (blocks `unsubscribe_wrapper_class`, `unsubscribe_header`, `unsubscribe_choices`,
  `unsubscribe_all`, `unsubscribe_buttons`) and `saved.html.twig`: unsubscribe page with template types,
- `error.html.twig`: invalid token.