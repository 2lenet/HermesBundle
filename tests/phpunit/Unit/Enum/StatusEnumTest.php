<?php

namespace phpunit\Unit\Enum;

use Lle\HermesBundle\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusEnumTest
 * @package phpunit\Unit\Enum
 *
 * @author 2LE <2le@2le.net>
 */
class StatusEnumTest extends TestCase
{
    public function testGetConstants(): void
    {
        self::assertEquals([
            'ERROR' => 'error',
            'DRAFT' => 'draft',
            'SENT' => 'sent',
            'SENDING' => 'sending',
            'UNSUBSCRIBED' => 'unsubscribed'
        ], StatusEnum::getConstants());
    }

    public function testIsValidName(): void
    {
        self::assertTrue(StatusEnum::isValidName('ERROR'));
        self::assertFalse(StatusEnum::isValidName('ERROR_FALSE'));

        self::assertTrue(StatusEnum::isValidName('UNSUBSCRIBED'));
        self::assertTrue(StatusEnum::isValidName('unsubscribed'));
    }

    public function testIsValidValue(): void
    {
        self::assertTrue(StatusEnum::isValidValue(StatusEnum::ERROR));
        self::assertTrue(StatusEnum::isValidValue(StatusEnum::UNSUBSCRIBED));
        self::assertFalse(StatusEnum::isValidValue(strtoupper(StatusEnum::SENT)));
    }
}
