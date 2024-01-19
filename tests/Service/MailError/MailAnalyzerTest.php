<?php

namespace App\Tests\Service\MailError;

use Lle\HermesBundle\Service\MailError\MailAnalyzer;
use PHPUnit\Framework\TestCase;

class MailAnalyzerTest extends TestCase
{
    private MailAnalyzer $analyzer;

    public function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new MailAnalyzer();
    }

    public function testIsErrorMail(): void
    {
        $subject = 'Undelivered Mail Returned to Sender';

        self::assertTrue($this->analyzer->isErrorMail($subject));
    }

    public function testIsErrorMailNotOk(): void
    {
        $subject = 'Newsletter';

        self::assertFalse($this->analyzer->isErrorMail($subject));
    }
}
