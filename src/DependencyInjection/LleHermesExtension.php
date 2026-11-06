<?php

namespace Lle\HermesBundle\DependencyInjection;

use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;
use Lle\HermesBundle\Repository\TranslatableTemplateRepository;
use Lle\HermesBundle\Service\MailBuilder;
use Lle\HermesBundle\Translatable\TranslatableTemplate;
use Lle\HermesBundle\Twig\HermesConsentExtension;
use Lle\HermesBundle\Twig\HermesExtension;
use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Lle\HermesBundle\Validator\TwigSyntaxValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Sandbox\SecurityPolicy;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class LleHermesExtension
 * @package Lle\HermesBundle\DependencyInjection
 *
 * @author 2LE <2le@2le.net>
 */
class LleHermesExtension extends Extension implements PrependExtensionInterface
{
    public const MAIL_TWIG_SERVICE = 'lle_hermes.mail_twig';

    /**
     * Extensions of the application kept in the mail environment when they exist: routing for
     * `url()`/`path()`, translation for `trans`, Intl for `format_currency` and friends.
     */
    public const OPTIONAL_MAIL_TWIG_EXTENSIONS = [
        'twig.extension.routing',
        'twig.extension.trans',
        'twig.extension.intl',
    ];

    /**
     * Services rendering or compiling mail templates, all given the mail environment.
     */
    public const MAIL_TWIG_CONSUMERS = [
        MailBuilder::class,
        HermesExtension::class,
        TwigSyntaxValidator::class,
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lle_hermes.root_dir', $processedConfig['root_dir']);
        $container->setParameter('lle_hermes.attachment_path', $processedConfig['attachment_path']);
        $container->setParameter('lle_hermes.upload_path', $processedConfig['upload_path']);
        $container->setParameter('lle_hermes.app_secret', $processedConfig['app_secret']);
        $container->setParameter('lle_hermes.app_domain', $processedConfig['app_domain']);
        $container->setParameter('lle_hermes.bounce_host', $processedConfig['bounce_host']);
        $container->setParameter('lle_hermes.bounce_port', $processedConfig['bounce_port']);
        $container->setParameter('lle_hermes.bounce_user', $processedConfig['bounce_user']);
        $container->setParameter('lle_hermes.bounce_password', $processedConfig['bounce_password']);
        $container->setParameter('lle_hermes.menu_icons', $processedConfig['menu_icons']);
        $container->setParameter('lle_hermes.recipient_error_retry', $processedConfig['recipient_error_retry']);
        $container->setParameter('lle_hermes.recipient_max_retry', $processedConfig['recipient_max_retry']);
        $container->setParameter('lle_hermes.tenant_class', $processedConfig['tenant_class']);
        $container->setParameter(
            'lle_hermes.attachment_nb_days_before_deletion',
            $processedConfig['attachment_nb_days_before_deletion']
        );
        $container->setParameter('lle_hermes.translatable_mail', $processedConfig['translatable_mail']);

        $translatableMail = $processedConfig['translatable_mail'];
        $templateClass = $translatableMail ? TranslatableTemplate::class : Template::class;
        $repoClass = $translatableMail ? TranslatableTemplateRepository::class : TemplateRepository::class;

        $container->setParameter('lle_hermes.template_class', $templateClass);
        $container->setAlias(TemplateRepositoryInterface::class, $repoClass)->setPublic(false);

        $this->registerMailTwigEnvironment($container, $processedConfig['mail_twig']);

        // Load the templates for the Hermes form types
        if ($container->hasParameter('twig.form.resources')) {
            /** @var array $parameter */
            $parameter = $container->getParameter('twig.form.resources');

            $container->setParameter(
                'twig.form.resources',
                array_merge(
                    ['@LleHermes/form/custom_types.html.twig'],
                    $parameter
                )
            );
        }
    }

    /**
     * @param array{
     *     extensions: string[],
     *     sandbox: array{
     *         enabled: bool,
     *         allowed_tags: string[],
     *         allowed_filters: string[],
     *         allowed_functions: string[],
     *     },
     * } $config
     */
    private function registerMailTwigEnvironment(ContainerBuilder $container, array $config): void
    {
        $extensions = [new Reference(HermesConsentExtension::class)];
        foreach (self::OPTIONAL_MAIL_TWIG_EXTENSIONS as $serviceId) {
            $extensions[] = new Reference($serviceId, ContainerInterface::IGNORE_ON_INVALID_REFERENCE);
        }
        foreach ($config['extensions'] as $serviceId) {
            $extensions[] = new Reference($serviceId);
        }

        $securityPolicy = null;
        $sandbox = $config['sandbox'];
        if ($sandbox['enabled']) {
            $securityPolicy = (new Definition(SecurityPolicy::class))
                ->setFactory([MailSecurityPolicyFactory::class, 'create'])
                ->setArguments([$sandbox['allowed_tags'], $sandbox['allowed_filters'], $sandbox['allowed_functions']]);
        }

        $container->register(MailTwigEnvironment::class, MailTwigEnvironment::class)
            ->setArguments([$extensions, $securityPolicy]);
        $container->setAlias(self::MAIL_TWIG_SERVICE, MailTwigEnvironment::class)->setPublic(true);

        $mailTwig = new Reference(MailTwigEnvironment::class);
        foreach (self::MAIL_TWIG_CONSUMERS as $serviceId) {
            $container->getDefinition($serviceId)->setArgument('$twig', $mailTwig);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $translatableMail = true;
        foreach ($container->getExtensionConfig($this->getAlias()) as $config) {
            if (isset($config['translatable_mail'])) {
                $translatableMail = (bool) $config['translatable_mail'];
            }
        }

        $templateClass = $translatableMail
            ? 'Lle\\HermesBundle\\Translatable\\TranslatableTemplate'
            : 'Lle\\HermesBundle\\Entity\\Template';

        $container->prependExtensionConfig('lle_entity_file', [
            'configurations' => [
                'attached_file' => [
                    'class' => $templateClass,
                    'storage_adapter' => 'lle_entity_file.storage.default',
                    'role' => 'PUBLIC_ACCESS',
                ],
                'mail_attached_file' => [
                    'class' => 'Lle\\HermesBundle\\Entity\\Mail',
                    'storage_adapter' => 'lle_entity_file.storage.default',
                    'role' => 'ROLE_HERMES_MAIL_READATTACHMENT',
                ],
            ],
        ]);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => [
                    'Lle\\HermesBundle\\Contracts\\TemplateInterface' => $templateClass,
                ],
            ],
        ]);

        if ($translatableMail) {
            $translatableDir = dirname(__DIR__) . '/Translatable';
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'LleHermesTranslatable' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => $translatableDir,
                            'prefix' => 'Lle\\HermesBundle\\Translatable',
                            'alias' => 'LleHermesTranslatable',
                        ],
                    ],
                ],
            ]);
        }

        if ($translatableMail) {
            $hasDoctrineTranslatable = false;
            $hasStofDoctrineExtensions = false;
            foreach ($container->getExtensionConfig('doctrine') as $config) {
                if (isset($config['orm']['mappings']['translatable'])) {
                    $hasDoctrineTranslatable = true;
                    break;
                }
            }

            if (!$hasDoctrineTranslatable) {
                $translatableGedmoDir = '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translatable/Entity';
                $container->prependExtensionConfig('doctrine', [
                    'orm' => [
                        'mappings' => [
                            'translatable' => [
                                'is_bundle' => false,
                                'type' => 'attribute',
                                'prefix' => 'Gedmo\Translatable\Entity',
                                'dir' => $translatableGedmoDir,
                                'alias' => 'GedmoTranslatable',
                            ],
                        ],
                    ],
                ]);
            }

            foreach ($container->getExtensionConfig('stof_doctrine_extensions') as $config) {
                if (isset($config['orm']['default']['translatable'])) {
                    $hasStofDoctrineExtensions = true;
                    break;
                }
            }

            if (!$hasStofDoctrineExtensions) {
                $container->prependExtensionConfig('stof_doctrine_extensions', [
                    'orm' => [
                        'default' => [
                            'translatable' => true,
                        ],
                    ],
                ]);
            }
        }
    }
}
