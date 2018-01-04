<?php

namespace Sherlockode\UserConfirmationBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Sherlockode\UserConfirmationBundle\Manager\MailManager;
use Symfony\Component\Security\Core\User\UserInterface;

class UserListener
{
    /**
     * @var MailManager
     */
    private $mailManager;

    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof UserInterface) {
            return;
        }

        $this->mailManager->sendAccountConfirmationEmail($object);
    }
}
