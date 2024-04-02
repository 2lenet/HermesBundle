<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Contracts\AttachmentInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Exception\AttachmentCreationException;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\Factory\AttachmentFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AttachmentService
{
    public const ATTACHMENTS_DIR = 'mail-%s/';

    protected string $attachmentsPath;
    protected array $attachments = [];
    protected string $rootDir;

    public function __construct(ParameterBagInterface $parameters)
    {
        /** @var string $rootDir */
        $rootDir = $parameters->get('lle_hermes.root_dir');
        $this->rootDir = $rootDir;

        /** @var string $attachmentsPath */
        $attachmentsPath = $parameters->get('lle_hermes.attachment_path');
        $this->attachmentsPath = $attachmentsPath;
    }

    public function saveAttachments(MailDto $mailDto, Mail $mail): Mail
    {
        foreach ($mailDto->getAttachments() as $attachment) {
            $this->saveAttachment($attachment, $mail);
        }

        $mail->setAttachement($this->attachments);

        return $mail;
    }

    public function deleteAttachements(Mail $mail): void
    {
        $path = $this->getAttachmentPath($mail);
        $this->delete($path);
    }

    protected function saveAttachment(AttachmentInterface $attachment, Mail $mail): void
    {
        $path = $this->getAttachmentPath($mail);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $this->createAttachmentFile($attachment, $path);

        $this->attachments[] = [
            'path' => $path,
            'name' => $attachment->getName(),
            'content-type' => $attachment->getContentType(),
        ];
    }

    /**
     * @throws AttachmentCreationException
     */
    protected function createAttachmentFile(AttachmentInterface $attachment, string $path): void
    {
        $filename = $path . $attachment->getName();

        if (!file_put_contents($filename, $attachment->getData())) {
            throw new AttachmentCreationException($attachment->getName());
        }
    }

    protected function getAttachmentPath(Mail $mail): string
    {
        $attachmentsDir = $this->rootDir . $this->attachmentsPath . self::ATTACHMENTS_DIR;
        $path = sprintf($attachmentsDir, $mail->getId());

        return $path;
    }

    protected function delete(string $path): bool
    {
        if (file_exists($path)) {
            /** @var array $files */
            $files = scandir($path);
            $files = array_diff($files, ['.', '..']);
            foreach ($files as $file) {
                if (is_dir($path . '/' . $file)) {
                    $this->delete($path . '/' . $file);
                } else {
                    unlink($path . '/' . $file);
                }
            }

            return rmdir($path);
        }

        return false;
    }
}
