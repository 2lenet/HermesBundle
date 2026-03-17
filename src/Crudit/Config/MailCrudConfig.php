<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Brick\TabBrick\TabConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\EditAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\EntityFileBundle\Crudit\Brick\EntityFileBrickConfig;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Component\HttpFoundation\RequestStack;

class MailCrudConfig extends AbstractCrudConfig
{
    public const string MAIL_ATTACHED_FILE_CONFIG = 'mail_attached_file';
    public const string ACTION_SEND = 'send';
    public const string ACTION_SEND_TEST = 'send_test';
    public const string ACTION_CANCEL = 'cancel';

    public function __construct(
        MailDatasource $datasource,
        protected RequestStack $requestStack,
        protected RecipientCrudConfig $recipientCrudConfig,
        protected LinkCrudConfig $linkCrudConfig,
    ) {
        $this->datasource = $datasource;
    }

    public function getName(): string
    {
        return 'HERMES_MAIL';
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
        $recipients = Field::new('countRecipients')->setSortable(false);
        $sendingDate = Field::new('sendingDate');
        $sendAtDate = Field::new('sendAtDate');
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
                $sendAtDate,
                $status,
                $statistics,
                $openingRate,
                $sendingRate,
                $html,
                $attachement,
            ];

            $request = $this->requestStack->getMainRequest();
            $route = $this->getPath(CrudConfigInterface::SHOW)->getRoute();
            if ($request && $route == $request->attributes->get('_route')) {
                /** @var Mail $mail */
                $mail = $this->datasource->get($request->attributes->get('id'));
                if ($mail->getTemplate()?->hasStatistics()) {
                    $fields = [
                        $subject,
                        $sendingDate,
                        $status,
                        $statistics,
                        $openingRate,
                        $linkOpeningRate,
                        $linksOpening,
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
            $sendAtDate,
            $sendingDate,
            $status,
        ];
    }

    public function getTabConfig(): ?TabConfig
    {
        $tabs = parent::getTabConfig();
        if (!$tabs) {
            $tabs = TabConfig::new();
        }

        $tabs->add(
            'tab.recipients',
            SublistConfig::new('mail', $this->recipientCrudConfig)
                ->setFields($this->recipientCrudConfig->getSublistFields())
                ->setActions($this->recipientCrudConfig->getSublistAction()),
        );
        $tabs->add(
            'tab.cc_recipients',
            SublistConfig::new('ccMail', $this->recipientCrudConfig)
                ->setFields($this->recipientCrudConfig->getSublistFields())
                ->setActions($this->recipientCrudConfig->getSublistAction()),
        );
        $tabs->add(
            'tab.links',
            SublistConfig::new('mail', $this->linkCrudConfig)
                ->setFields($this->linkCrudConfig->getSublistFields())
                ->setActions($this->linkCrudConfig->getSublistAction()),
            displayIf: fn(Mail $mail) => $mail->getTemplate()?->hasStatistics(),
        );
        $tabs->add(
            'tab.attached_files',
            EntityFileBrickConfig::new(self::MAIL_ATTACHED_FILE_CONFIG),
            displayIf: fn(Mail $mail) => !$mail->hasAttachmentsDeleted(),
        );

        return $tabs;
    }

    public function getListActions(): array
    {
        $actions = parent::getListActions();
        unset($actions[CrudConfigInterface::ACTION_ADD]);

        return $actions;
    }

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();

        $actions[CrudConfigInterface::ACTION_EDIT] = EditAction::new(
            'action.edit',
            new Path('lle_hermes_crudit_mail_edit'),
            Icon::new('edit')
        )
            ->setDisplayIf((fn(Mail $mail) => $mail->getStatus() === Mail::STATUS_DRAFT))
            ->setCssClass('btn btn-secondary btn-sm crudit-action')
            ->setRole('ROLE_HERMES_MAIL_EDIT');

        $actions[self::ACTION_SEND] = ItemAction::new(
            'action.send',
            new Path('lle_hermes_crudit_mail_send'),
            Icon::new('paper-plane')
        )
            ->setRole('ROLE_HERMES_MAIL_SEND')
            ->setCssClass('btn btn-success btn-sm mr-1')
            ->setModal('@LleHermes/modal/_confirm_send_mail.html.twig');

        $actions[self::ACTION_SEND_TEST] = ItemAction::new(
            'action.sendmailtest',
            (Path::new('lle_hermes_crudit_mail_send_testmail')),
            Icon::new('fas fa-envelope')
        )
            ->setRole('ROLE_HERMES_MAIL_SENDTESTMAIL')
            ->setCssClass('btn btn-warning btn-sm mr-1')
            ->setModal('@LleHermes/crud/mail/_modal_send_testmail.html.twig');

        $actions[self::ACTION_CANCEL] = ItemAction::new(
            'action.cancel',
            Path::new('lle_hermes_crudit_mail_cancel'),
            Icon::new('ban'),
        )
            ->setDisplayIf((fn(Mail $mail) => $mail->getStatus() === Mail::STATUS_SENDING))
            ->setRole('ROLE_HERMES_MAIL_CANCEL')
            ->setCssClass('btn btn-danger btn-sm')
            ->setConfirmModal(true);

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = [];
        $parentActions = parent::getShowActions();

        $actions[CrudConfigInterface::ACTION_LIST] = $parentActions[CrudConfigInterface::ACTION_LIST];

        $actions[CrudConfigInterface::ACTION_EDIT] = EditAction::new(
            'action.edit',
            new Path('lle_hermes_crudit_mail_edit'),
            Icon::new('edit')
        )
            ->setDisplayIf((fn(Mail $mail) => $mail->getStatus() === Mail::STATUS_DRAFT))
            ->setCssClass('btn btn-secondary btn-sm crudit-action ms-1')
            ->setRole('ROLE_HERMES_MAIL_EDIT');

        $actions[self::ACTION_SEND] = ItemAction::new(
            'action.send',
            new Path('lle_hermes_crudit_mail_send'),
            Icon::new('paper-plane')
        )
            ->setRole('ROLE_HERMES_MAIL_SEND')
            ->setCssClass('btn btn-success btn-sm ms-1')
            ->setModal('@LleHermes/modal/_confirm_send_mail.html.twig');

        $actions[self::ACTION_SEND_TEST] = ItemAction::new(
            'action.sendmailtest',
            (Path::new('lle_hermes_crudit_mail_send_testmail')),
            Icon::new('fas fa-envelope')
        )
            ->setRole('ROLE_HERMES_MAIL_SENDTESTMAIL')
            ->setCssClass('btn btn-warning btn-sm ms-1')
            ->setModal('@LleHermes/crud/mail/_modal_send_testmail.html.twig');

        $actions[self::ACTION_CANCEL] = ItemAction::new(
            'action.cancel',
            Path::new('lle_hermes_crudit_mail_cancel'),
            Icon::new('ban'),
        )
            ->setDisplayIf((fn(Mail $mail) => $mail->getStatus() === Mail::STATUS_SENDING))
            ->setRole('ROLE_HERMES_MAIL_CANCEL')
            ->setCssClass('btn btn-danger btn-sm ms-1')
            ->setConfirmModal(true);

        $actions[CrudConfigInterface::ACTION_DELETE] = $parentActions[CrudConfigInterface::ACTION_DELETE];

        return $actions;
    }

    public function getDefaultSort(): array
    {
        return [['id', 'DESC']];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
