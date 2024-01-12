# Multitenant

This bundle includes an multitenant management.
You can enable the multitenant mode following this instructions:

## Configuration
Add the FCQN in `tenant_class` line in `config/packages/lle_hermes.yaml`:

```yaml
# config/packages/lle_hermes.yaml
lle_hermes:
    tenant_class: App\Entity\Establishment
```

In your user entity implements the `MultiTenantInterface` with the method `getTenantId()`:

```php
// src/Entity/User.php
<?php

namespace App\Entity;

use Lle\HermesBundle\Contracts\MultiTenantInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements MultiTenantInterface
{
    // ...
    public function getTenantId(): int
    {
        return $this->getEstablishment()->getId();
    }
}
```

Now the dashboard, mail and recipient sections are filtred by establishment.
