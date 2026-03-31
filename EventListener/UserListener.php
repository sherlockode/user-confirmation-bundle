<?php

namespace Sherlockode\UserConfirmationBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sherlockode\UserConfirmationBundle\Manager\MailManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
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
    public function __construct(
        MailManagerInterface $mailManager,
        #[Autowire(service: 'fos_user.util.token_generator')]
        TokenGeneratorInterface $tokenGenerator,
    ) {
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
