# Translatable mails

Hermes can send the same template in different languages. The translatable
fields of a `Template` are stored per locale (using
[Gedmo Translatable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md)),
and the locale to use is chosen **at send time**, on the `MailDto`.

This page covers the developer side: how to send a mail in a given locale once
the feature is enabled. For enabling/disabling the feature and the related
Doctrine/Stof configuration, see [configuration.md](configuration.md#translatable-templates).

## Prerequisites

- `translatable_mail` must be `true` (the default). When it is `false`, the
  locale is ignored and the base `Template` fields are always used. See
  [configuration.md](configuration.md#translatable-templates).
- The template must have translations filled in for the target locale. They are
  edited per locale from the template form in the back-office (the translatable
  fields render as `GedmoTranslatableType` widgets when the feature is on).

## Translatable fields

Only these `Template` fields are translated per locale:

`libelle`, `subject`, `senderName`, `senderEmail`, `text`, `html`, `mjml`.

The template `code` is **not** translatable: it stays the unique identifier you
pass to `MailDto::setTemplate()`, regardless of the locale.

## Send a mail in a given locale

Set the locale on the `MailDto` with `setLocale()`. Everything else is identical
to a regular send (see [usage.md](usage.md)).

```php
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Model\ContactDto;
use Lle\HermesBundle\Service\Mailer;

public function __construct(
    private readonly Mailer $mailer,
) {
}

public function sendUserMail(User $user): void
{
    $mail = new MailDto();
    $mail
        ->setTemplate('TEMPLATE_CODE')
        ->setLocale($user->getLocale()) // 'fr', 'en', ...
        ->setData(['url' => 'https://my.custom.url']);

    $mail->addTo(new ContactDto($user->getFullName(), $user->getEmail()));

    $this->mailer->create($mail);
}
```

The locale you set is the locale the recipient will receive. A common pattern is
to use the recipient's own locale (`$user->getLocale()`), the current request
locale (`$request->getLocale()`), or a fixed value for a given audience.

`setLocale()` works the same way with `create()`, `create($mail, MailDto::DRAFT)`
and `send()`.

## How the locale is resolved

When the mail is built (`MailFactory::createMailFromDto()`), each translatable
field is resolved through `MailFactory::getValueFromLocale()`:

```php
public function getValueFromLocale(Template $template, string $field, ?string $locale): ?string
{
    if ($this->translatableMail && $locale) {
        foreach ($template->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === $field) {
                if ($translation->getContent()) {
                    return $translation->getContent();
                } else {
                    break;
                }
            }
        }
    }

    return $this->propertyAccessor->getValue($template, $field);
}
```

The resolved values are copied onto the `Mail` entity, and the chosen locale is
persisted on `Mail::$locale` for traceability.

### Fallback to the base template

The base `Template` field value (the default-locale content) is used whenever:

- `translatable_mail` is `false`; **or**
- no locale was set on the `MailDto` (`setLocale()` not called → `null`); **or**
- the template has no translation for that `(locale, field)` pair; **or**
- the matching translation exists but its content is empty.

The fallback is per field: a partially translated template will mix translated
fields with base-template fields for the missing ones. There is no
locale-to-locale fallback (e.g. `fr_BE` does not fall back to `fr`): the locale
string must match the one stored on the translation exactly.

> **Tip** — if a recipient receives a mail in the wrong language, check that the
> template actually has a non-empty translation for that exact locale string;
> otherwise the base template content is sent.

## See also

- [Create and send mails](usage.md)
- [Configuration › Translatable templates](configuration.md#translatable-templates)
- [Create templates](templating.md)
</content>
</invoke>