<?php

namespace Lle\HermesBundle\Enum;

/**
 * Class StatusEnum
 * @package Lle\HermesBundle\Enum
 *
 * @author 2LE <2le@2le.net>
 */
class StatusEnum extends Enum
{
    public const ERROR = 'error';
    public const DRAFT = 'draft';
    public const SENDING = 'sending';
    public const SENT = 'sent';
    public const UNSUBSCRIBED = 'unsubscribed';
}
