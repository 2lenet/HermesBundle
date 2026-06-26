<?php

namespace Lle\HermesBundle\Contracts;

use Doctrine\Common\Collections\Collection;

interface TemplateInterface extends MultiTenantInterface
{
    public const string TYPE_HTML = 'html';
    public const string TYPE_CKEDITOR = 'ckeditor';
    public const string TYPE_MJML = 'mjml';

    public function getId(): ?int;

    public function setId(int $id): static;

    public function getLibelle(): ?string;

    public function setLibelle(string $libelle): static;

    public function getSubject(): ?string;

    public function setSubject(string $subject): static;

    public function getSenderName(): ?string;

    public function setSenderName(?string $senderName): static;

    public function getSenderEmail(): ?string;

    public function setSenderEmail(string $senderEmail): static;

    public function getText(): ?string;

    public function setText(?string $text): static;

    public function getCode(): ?string;

    public function setCode(string $code): static;

    public function getHtml(): ?string;

    public function setHtml(?string $html): static;

    public function isUnsubscriptions(): bool;

    public function setUnsubscriptions(bool $unsubscriptions): static;

    public function hasStatistics(): bool;

    public function setStatistics(bool $statistics): static;

    public function hasSendToErrors(): bool;

    public function setSendToErrors(bool $sendToErrors): static;

    public function setTenantId(?int $tenantId): static;

    public function getCustomBounceEmail(): ?string;

    public function setCustomBounceEmail(?string $customBounceEmail): static;

    public function getType(): string;

    public function setType(string $type): void;

    public function getMjml(): ?string;

    public function setMjml(?string $mjml): void;

    public function getTranslations(): Collection;
}
