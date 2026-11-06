<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Twig;

use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicyInterface;

/**
 * Twig environment used to render mail templates (subject, sender name, html, text).
 *
 * Mail templates are written by users from the back office: they must not be compiled with the
 * application environment, which exposes its globals (`app.request`, environment variables…),
 * its template files (`include`, `source`) and every Twig function of the project.
 *
 * This environment only loads strings, has no global, never fails on an unknown variable and
 * only knows the extensions it is given. With a security policy, every template is sandboxed.
 */
class MailTwigEnvironment extends Environment
{
    public const OPTIONS = [
        'strict_variables' => false,
        'autoescape' => 'html',
        'cache' => false,
    ];

    /**
     * @param iterable<ExtensionInterface> $extensions
     */
    public function __construct(iterable $extensions = [], ?SecurityPolicyInterface $securityPolicy = null)
    {
        parent::__construct(new ArrayLoader(), self::OPTIONS);

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        if ($securityPolicy !== null) {
            $this->addExtension(new SandboxExtension($securityPolicy, true));
        }
    }
}
