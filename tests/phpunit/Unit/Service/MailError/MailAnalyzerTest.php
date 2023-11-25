<?php

namespace phpunit\Unit\Service\MailError;

use Lle\HermesBundle\Service\MailError\MailAnalyzer;
use PHPUnit\Framework\TestCase;

class MailAnalyzerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new MailAnalyzer();
    }

    function testIsErrorMail(): void
    {
        $subject = 'Undelivered Mail Returned to Sender';

        self::assertTrue($this->analyzer->isErrorMail($subject));
    }

    function testIsErrorMailNotOk(): void
    {
        $subject = 'Newsletter';

        self::assertFalse($this->analyzer->isErrorMail($subject));
    }
}
