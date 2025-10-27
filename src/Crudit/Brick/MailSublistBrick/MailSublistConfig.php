<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Brick\MailSublistBrick;

use Lle\CruditBundle\Brick\AbstractBrickConfig;

class MailSublistConfig extends AbstractBrickConfig
{
    public static function new(): self
    {
        return new self();
    }
}
