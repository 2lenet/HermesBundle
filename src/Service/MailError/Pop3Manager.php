<?php

namespace Lle\HermesBundle\Service\MailError;

use IMAP\Connection;

class Pop3Manager
{
    public const DEFAULT_FOLDER = 'INBOX';

    protected Connection $imap;

    public function __construct(
        protected readonly string $host,
        protected readonly string $port,
        protected readonly string $user,
        protected readonly string $password,
    ) {
    }

    public function login(string $folder = self::DEFAULT_FOLDER, bool $ssl = false): bool
    {
        try {
            $mailbox = $this->getMailBoxInfos($folder, $ssl);
            $imap = imap_open($mailbox, $this->user, $this->password);
        } catch (\ErrorException $exception) {
            return false;
        }

        if (!$imap) {
            return false;
        }

        $this->imap = $imap;

        return true;
    }

    public function logout(): bool
    {
        return imap_close($this->imap, CL_EXPUNGE);
    }

    public function getMails(int $limit): array
    {
        $sequence = '1:' . $limit;

        $mails = imap_fetch_overview($this->imap, $sequence);
        if (!$mails) {
            return [];
        }

        return $mails;
    }

    public function getMailContent(int $uid): ?string
    {
        $body = imap_body($this->imap, $uid, FT_UID);
        if (!$body) {
            return null;
        }

        return $body;
    }

    public function deleteMail(int $uid): bool
    {
        return imap_delete($this->imap, (string)$uid, FT_UID);
    }

    protected function getMailBoxInfos(string $folder = self::DEFAULT_FOLDER, bool $ssl = false): string
    {
        if (!$ssl) {
            $ssl = '/novalidate-cert';
        } else {
            $ssl = '';
        }

        return '{' . $this->host . ':' . $this->port . '/pop3' . $ssl . '}' . $folder;
    }
}
