<?php

namespace Sherlockode\UserConfirmationBundle\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sherlockode\UserConfirmationBundle\Manager\MailManagerInterface;

class UserListener
{
    /**
     * @var MailManagerInterface
     */
    private $mailManager;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @param MailManagerInterface    $mailManager
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(MailManagerInterface $mailManager, TokenGeneratorInterface $tokenGenerator)
    {
        $this->mailManager = $mailManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @param PrePersistEventArgs $args
     */
    public function prePersist(PrePersistEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof UserInterface || $object->isEnabled()) {
            return;
        }

        $object->setEnabled(false);
        if (null === $object->getConfirmationToken()) {
            $object->setConfirmationToken($this->tokenGenerator->generateToken());
        }
    }
    /**
     * @param PostPersistEventArgs $args
     */
    public function postPersist(PostPersistEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof UserInterface || $object->isEnabled()) {
            return;
        }

        $this->mailManager->sendAccountConfirmationEmail($object);
    }
}
