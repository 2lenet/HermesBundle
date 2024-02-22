<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\EditAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TemplateCrudConfig extends AbstractCrudConfig
{
    public const ACTION_DUPLICATE = 'duplicate';
    public const ACTION_COPY_FOR_TENANT = 'copy_for_tenant';

    protected ParameterBagInterface $parameterBag;

    public function __construct(
        TemplateDatasource $datasource,
        ParameterBagInterface $parameterBag,
    ) {
        $this->datasource = $datasource;
        $this->parameterBag = $parameterBag;
    }

    public function getName(): ?string
    {
        return "HERMES_TEMPLATE";
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $libelle = Field::new('libelle');
        $subject = Field::new('subject');
        $senderName = Field::new('senderName');
        $senderEmail = Field::new('senderEmail');
        $text = Field::new('text');
        $code = Field::new('code');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_template_html.html.twig')
            ->setCssClass('col-12');
        $unsubscriptions = Field::new('unsubscriptions');
        $statistics = Field::new('statistics');
        $customBounceEmail = Field::new('customBounceEmail');

        if ($key == CrudConfigInterface::INDEX) {
            return [
                $libelle,
                $subject,
                $senderName,
                $senderEmail,
                $code,
                $unsubscriptions,
                $statistics,
            ];
        }

        return [
            $libelle,
            $subject,
            $senderName,
            $senderEmail,
            $text,
            $code,
            $html,
            $unsubscriptions,
            $statistics,
            $customBounceEmail,
        ];
    }

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();

        $actions[self::ACTION_DUPLICATE] = ItemAction::new(
            'crud.action.duplicate',
            Path::new('lle_hermes_template_duplicate')->setRole('ROLE_HERMES_TEMPLACE_DUPLICATE'),
            Icon::new('clone')
        )->setDropdown(true);

        if ($this->parameterBag->get('lle_hermes.tenant_class')) {
            $actions[self::ACTION_COPY_FOR_TENANT] = ItemAction::new(
                'crud.action.copy_for_tenant',
                Path::new('lle_hermes_crudit_template_copyfortenant')->setRole('ROLE_HERMES_COPY_FOR_TENANT'),
                Icon::new('share')
            )->setDropdown(true);
        }

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_template';
    }
}
