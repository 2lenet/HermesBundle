<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Model;

/**
 * A mail template field that cannot be rendered by the mail Twig environment.
 */
class TemplateIssue
{
    public function __construct(
        protected string $source,
        protected ?int $id,
        protected string $field,
        protected string $error,
    ) {
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
