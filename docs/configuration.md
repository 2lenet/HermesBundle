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

Make sure that repositories exist and that they have the correct rights !
