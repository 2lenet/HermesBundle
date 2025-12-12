<?php

namespace Lle\HermesBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Entity\EntityFile;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MailAttachmentVoter extends Voter
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameterBag,
        protected Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            'ROLE_HERMES_MAIL_READATTACHMENT'
        ]);
    }

    /**
     * @param EntityFile $subject
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        if (!$this->security->isGranted('ROLE_HERMES_MAIL_SHOW')) {
            return false;
        }

        if ($this->isMultiTenantEnabled()) {
            $tenantId = $this->getTenantId($token);
            $mail = $this->em->getRepository(Mail::class)->find($subject->getEntityId());
            if ($tenantId && $mail && $mail->getTenantId() !== $tenantId) {
                return false;
            }
        }

        return true;
    }

    public function isMultiTenantEnabled(): bool
    {
        if ($this->parameterBag->get('lle_hermes.tenant_class')) {
            return true;
        }

        return false;
    }

    public function getTenantId(TokenInterface $token): ?int
    {
        /** @var ?MultiTenantInterface $user */
        $user = $token->getUser();

        return $user?->getTenantId();
    }
}
