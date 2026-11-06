<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Twig;

use Twig\Sandbox\SecurityPolicy;

/**
 * Sandbox policy of mail templates: what a template written from the back office may use.
 *
 * Template data are arrays (they are stored as JSON on the mail), which the sandbox never
 * restricts: every key passed to a template stays readable. What is restricted is the rest —
 * tags, filters, functions, tests, and methods/properties of objects (none allowed).
 *
 * Projects complete these lists through `lle_hermes.mail_twig.sandbox`; the defaults below are
 * always kept.
 */
class MailSecurityPolicyFactory
{
    public const DEFAULT_TAGS = ['if', 'for', 'set'];

    public const DEFAULT_FILTERS = [
        // twig/twig
        'abs', 'capitalize', 'date', 'default', 'e', 'escape', 'first', 'format', 'join', 'keys',
        'last', 'length', 'lower', 'merge', 'nl2br', 'number_format', 'raw', 'replace', 'reverse',
        'round', 'slice', 'split', 'striptags', 'title', 'trim', 'upper', 'url_encode',
        // twig/intl-extra
        'format_currency', 'format_date', 'format_datetime', 'format_number', 'format_time',
        // symfony/twig-bridge
        'trans',
    ];

    public const DEFAULT_FUNCTIONS = [
        // twig/twig
        'date', 'max', 'min', 'range',
        // symfony/twig-bridge
        'path', 'url',
        // lle/hermes-bundle
        'hermes_has_consent',
    ];

    /**
     * @param string[] $tags      added to DEFAULT_TAGS
     * @param string[] $filters   added to DEFAULT_FILTERS
     * @param string[] $functions added to DEFAULT_FUNCTIONS
     */
    public static function create(array $tags = [], array $filters = [], array $functions = []): SecurityPolicy
    {
        $policy = new SecurityPolicy(
            allowedTags: self::merge(self::DEFAULT_TAGS, $tags),
            allowedFilters: self::merge(self::DEFAULT_FILTERS, $filters),
            allowedFunctions: self::merge(self::DEFAULT_FUNCTIONS, $functions),
        );

        // Opt in to the Twig 4 behaviour: no tag, function or test is allowed implicitly.
        $policy->setStrict(true);

        return $policy;
    }

    /**
     * @param string[] $defaults
     * @param string[] $additions
     *
     * @return string[]
     */
    private static function merge(array $defaults, array $additions): array
    {
        return array_values(array_unique([...$defaults, ...$additions]));
    }
}
