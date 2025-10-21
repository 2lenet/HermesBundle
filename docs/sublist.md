# Sublist

This bundle includes a sublist of mails to have a list of mails linked to an entity.

## Configuration
Add the following code in the `EntityCrudConfig.php`:

```php
// src/Crudit/Config/EntityCrudConfig.php

use Lle\HermesBundle\Crudit\Brick\MailSublistBrick\MailSublistConfig;

// ...

public function getTabs(): array
{
    return [
        'tab.mails' => MailSublistConfig::new(),
    ];
}
```

Now the mails linked to an entity of your project are displayed into a sublist.
