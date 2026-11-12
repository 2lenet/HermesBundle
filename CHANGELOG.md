# Changelog

## 1.15.0 (unreleased)

Security release: mail templates can no longer reach the application through Twig. See [UPGRADE-1.15.md](UPGRADE-1.15.md).

- Render mail templates with a dedicated Twig environment (`MailTwigEnvironment`, service `lle_hermes.mail_twig`):
  no globals, no template files, only Twig core, Hermes consent and — when present — routing, translation and Intl
  extensions, plus the extensions listed in `lle_hermes.mail_twig.extensions`.
- Sandbox mail templates by default (`lle_hermes.mail_twig.sandbox`), with an allow-list completed per project.
- `MailTwigEnvironment`: keep the application runtime loader, so that project extensions using a runtime class work;
  `checkTemplate()` checks a template against the sandbox without running it.
- `TwigSyntaxValidator`: check with the mail environment without running the template, report forbidden constructs,
  validate `subject` and `senderName` too.
- Back office: a template that cannot be rendered shows `Template error: …` instead of a server error
  (`MailPreviewTemplater`, returned by `lle_hermes_get_templater`).
- Add the `lle:hermes:check-templates` command: lists templates, template translations and mails not sent yet that
  the sandbox refuses (mails read by batches, mails sharing the same content reported once).
- Deprecate passing an environment other than `MailTwigEnvironment` to `MailTemplater`.
- `Sender`: a template error puts the recipient in `error` with its reason, without retry and without failing the
  other recipients of the mail.
- `MailTemplater`: restore the strictness of the given environment after rendering instead of forcing it on in debug.
- Require `twig/twig` ^3.28.
