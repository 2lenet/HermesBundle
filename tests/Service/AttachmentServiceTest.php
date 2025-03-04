<?php

namespace App\Tests\Service;

use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Dto\Base64AttachmentDto;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\AttachmentService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class AttachmentServiceTest extends TestCase
{
    protected AttachmentService $attachmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $parameters = new ParameterBag([
            'lle_hermes.root_dir' => __DIR__ . '/../',
            'lle_hermes.attachment_path' => 'data/attachments/',
        ]);
        $fileLoader = $this->getMockBuilder(EntityFileLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attachmentService = new AttachmentService($parameters, $fileLoader);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $filepath = __DIR__ . '/../data/attachments/mail-1/';
        if (file_exists($filepath)) {
            chmod($filepath, 0777);
            $this->attachmentService->delete($filepath);
        }
    }

    public function testSaveAttachments(): void
    {
        $mailDto = $this->createMailDto();
        $mail = $this->createMail(1);

        $this->attachmentService->saveAttachments($mailDto, $mail);

        $expected = [
            [
                'path' => __DIR__ . '/../data/attachments/mail-1/',
                'name' => 'test-attachment.pdf',
                'content-type' => 'application/pdf',
            ],
        ];

        self::assertEquals($expected, $mail->getAttachement());
        self::assertTrue(file_exists(__DIR__ . '/../data/attachments/mail-1/'));
    }

    public function testDeleteAttachments(): void
    {
        $mailDto = $this->createMailDto();
        $mail = $this->createMail(2);

        $this->attachmentService->saveAttachments($mailDto, $mail);
        self::assertTrue(file_exists(__DIR__ . '/../data/attachments/mail-2/'));

        $this->attachmentService->deleteAttachements($mail);
        self::assertFalse(file_exists(__DIR__ . '/../data/attachments/mail-2/'));
    }

    protected function createMailDto(): MailDto
    {
        $mailDto = new MailDto();
        $mailDto->setSubject('subject')
            ->setStatus(MailDto::DRAFT)
            ->setAttachments([$this->createBase64AttachmentDto()]);

        return $mailDto;
    }

    protected function createMail(int $id): Mail
    {
        $template = new Template();
        $mail = new Mail();
        $mail->setId($id)
            ->setSubject('subject')
            ->setStatus(Mail::STATUS_DRAFT)
            ->setTemplate($template);

        return $mail;
    }

    protected function createBase64AttachmentDto(): Base64AttachmentDto
    {
        return new Base64AttachmentDto(
            'attachment content',
            'test-attachment.pdf',
            'application/pdf',
        );
    }
}
