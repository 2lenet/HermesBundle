<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\DeleteAction;
use Lle\CruditBundle\Dto\Action\EditAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Component\HttpFoundation\RequestStack;

class MailCrudConfig extends AbstractCrudConfig
{
    private RequestStack $requestStack;
    private RecipientCrudConfig $recipientCrudConfig;
    private LinkCrudConfig $linkCrudConfig;

    public function __construct(MailDatasource $datasource, RequestStack $requestStack, RecipientCrudConfig $recipientCrudConfig, LinkCrudConfig $linkCrudConfig)
    {
        $this->datasource = $datasource;
        $this->requestStack = $requestStack;
        $this->recipientCrudConfig = $recipientCrudConfig;
        $this->linkCrudConfig = $linkCrudConfig;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject')->setTemplate('@LleHermes/crud/_subject.html.twig');
        $statistics = Field::new('totalToSend')
            ->setTemplate('@LleHermes/layout/_mail_statistics.html.twig')
            ->setLabel('field.mailStatistics');
        $recipients = Field::new('countRecipients');
        $sendingDate = Field::new('sendingDate');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $openingRate = Field::new('percentOpened')
            ->setTemplate('@LleHermes/layout/_percent.html.twig')
            ->setLabel('field.totalOpened');
        $sendingRate = Field::new('percentSent')
            ->setTemplate('@LleHermes/layout/_sending_rate.html.twig')
            ->setLabel('field.sendingRate');
        $linksOpening = Field::new('totalLinkOpening')->setLabel('field.nbopeningsLinks');
        $linkOpeningRate = Field::new('totalLinkOpeningRate')
            ->setTemplate('@LleHermes/layout/_percent.html.twig')
            ->setLabel('field.linkOpeningRate');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_html.html.twig')
            ->setCssClass('col-12');
        $attachement = Field::new('jsonAttachement')->setTemplate('@LleHermes/crud/_attachement.html.twig');

        if ($key == CrudConfigInterface::SHOW) {
            $fields = [
                $subject,
                $sendingDate,
                $status,
                $statistics,
                $openingRate,
                $sendingRate,
                $html,
                $attachement,
            ];

            $request = $this->requestStack->getMainRequest();
            if ($this->getPath(CrudConfigInterface::SHOW)->getRoute() == $request->attributes->get('_route')) {
                /** @var Mail $mail */
                $mail = $this->datasource->get($request->attributes->get('id'));
                if ($mail->getTemplate()->hasStatistics()) {
                    $fields = [
                        $subject,
                        $sendingDate,
                        $status,
                        $openingRate,
                        $linksOpening,
                        $linkOpeningRate,
                        $html,
                        $attachement,
                    ];
                }
            }

            return $fields;
        }

        return [
            $subject,
            $recipients,
            $sendingDate,
            $status,
        ];
    }

    public function getDefaultSort(): array
    {
        return [['id', 'DESC']];
    }

    public function getItemActions(): array
    {
        $actions = [];

        $actions[] = ItemAction::new(
            'action.send',
            new Path('lle_hermes_crudit_mail_send'),
            Icon::new('paper-plane')
        )
            ->setCssClass('btn btn-success btn-sm mr-1')
            ->setModal('@LleHermes/modal/_confirm_send_mail.html.twig');

        $actions[] = ItemAction::new(
            'action.show',
            $this->getPath(CrudConfigInterface::SHOW),
            Icon::new('search')
        )->setCssClass('btn btn-primary btn-sm mr-1');

        $actions[] = DeleteAction::new(
            'action.delete',
            $this->getPath(CrudConfigInterface::DELETE),
            Icon::new('trash-alt')
        )
            ->setCssClass('btn btn-danger btn-sm mr-1')
            ->setModal("@LleCrudit/modal/_confirm_delete.html.twig");

        $actions[] = ItemAction::new(
            'action.sendmailtest',
            (Path::new('lle_hermes_crudit_mail_send_testmail'))->setRole('ROLE_MAIL_SEND_TESTMAIL'),
            Icon::new('fas fa-envelope')
        )
            ->setCssClass('btn btn-warning btn-sm mr-1')
            ->setModal('@LleHermes/crud/Mail/_modal_send_testmail.html.twig');

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = [];

        $actions[] = ItemAction::new(
            'action.list',
            $this->getPath(CrudConfigInterface::INDEX),
            Icon::new('list')
        )->setCssClass('btn btn-secondary btn-sm mr-1');

        $actions[] = DeleteAction::new(
            'action.delete',
            $this->getPath(CrudConfigInterface::DELETE),
            Icon::new('trash-alt')
        )
            ->setCssClass('btn btn-danger btn-sm mr-1')
            ->setModal("@LleCrudit/modal/_confirm_delete.html.twig");

        return $actions;
    }

    public function getTabs(): array
    {
        $tabs = [
            'tab.recipients' => SublistConfig::new('mail', $this->recipientCrudConfig)
                ->setFields($this->recipientCrudConfig->getSublistFields())
                ->setActions($this->recipientCrudConfig->getSublistAction()),
        ];

        $request = $this->requestStack->getMainRequest();
        if ($this->getPath(CrudConfigInterface::SHOW)->getRoute() == $request->attributes->get('_route')) {
            /** @var Mail $mail */
            $mail = $this->datasource->get($request->attributes->get('id'));
            if ($mail->getTemplate()->hasStatistics()) {
                $tabs = [
                    'tab.recipients' => SublistConfig::new('mail', $this->recipientCrudConfig)
                        ->setFields($this->recipientCrudConfig->getSublistFields())
                        ->setActions($this->recipientCrudConfig->getSublistAction()),
                    'tab.links' => SublistConfig::new('mail', $this->linkCrudConfig)
                        ->setFields($this->linkCrudConfig->getSublistFields())
                        ->setActions($this->linkCrudConfig->getSublistAction())
                ];
            }
        }

        return $tabs;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
