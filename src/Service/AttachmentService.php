<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Contracts\AttachmentInterface;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Exception\AttachmentCreationException;
use Lle\HermesBundle\Model\MailDto;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AttachmentService
{
    public const ATTACHMENTS_DIR = 'mail-%s/';

    protected string $attachmentsPath;
    protected string $rootDir;

    public function __construct(
        ParameterBagInterface $parameters,
        protected EntityFileLoader $entityFileLoader,
        protected EntityManagerInterface $em,
    ) {
        /** @var string $rootDir */
        $rootDir = $parameters->get('lle_hermes.root_dir');
        $this->rootDir = $rootDir;

        /** @var string $attachmentsPath */
        $attachmentsPath = $parameters->get('lle_hermes.attachment_path');
        $this->attachmentsPath = $attachmentsPath;
    }

    public function saveAttachments(MailDto $mailDto, Mail $mail): Mail
    {
        $mailManager = $this->entityFileLoader->get(MailCrudConfig::MAIL_ATTACHED_FILE_CONFIG);
        foreach ($mailDto->getAttachments() as $attachment) {
            $entityFile = $mailManager->save($mail, $attachment->getData(), $attachment->getName());

            $this->em->persist($entityFile);
        }
        if ($mail->getTemplate()) {
            $manager = $this->entityFileLoader->get(TemplateCrudConfig::ATTACHED_FILE_CONFIG);
            foreach ($manager->get($mail->getTemplate()) as $file) {
                $entityFile = $mailManager->save($mail, $manager->read($file), $file->getName());

                $this->em->persist($entityFile);
            }
        }

        return $mail;
    }

    public function deleteAttachements(Mail $mail): void
    {
        $path = $this->getAttachmentPath($mail);
        $this->delete($path);
    }

    protected function saveAttachment(AttachmentInterface $attachment, Mail $mail): array
    {
        $path = $this->getAttachmentPath($mail);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $this->createAttachmentFile($attachment, $path);

        return [
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

    public function delete(string $path): bool
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
