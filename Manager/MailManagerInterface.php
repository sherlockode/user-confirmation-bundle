<?php

namespace Sherlockode\UserConfirmationBundle\Manager;

use FOS\UserBundle\Model\UserInterface;

interface MailManagerInterface
{
    /**
     * @param UserInterface $user
     *
     * @return bool true on success, false otherwise
     */
    public function sendAccountConfirmationEmail(UserInterface $user);
}
