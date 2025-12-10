<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Mail;
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
            $entityFile = $mailManager->save($mail, (string)$attachment->getData(), $attachment->getName());

            $this->em->persist($entityFile);
        }
        if ($mail->getTemplate()) {
            $manager = $this->entityFileLoader->get(TemplateCrudConfig::ATTACHED_FILE_CONFIG);
            foreach ($manager->get($mail->getTemplate()) as $file) {
                $entityFile = $mailManager->save($mail, $manager->read($file), (string)$file->getName());

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
