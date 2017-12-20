<?php

namespace Sherlockode\UserConfirmationBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Model\UserInterface;

class MailManager
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $confirmationEmailTemplate;

    /**
     * MailManager constructor.
     *
     * @param \Swift_Mailer         $mailer
     * @param EngineInterface       $templating
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $senderEmail
     * @param string                $confirmationEmailTemplate
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        UrlGeneratorInterface $urlGenerator,
        $senderEmail,
        $confirmationEmailTemplate
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->urlGenerator = $urlGenerator;
        $this->senderEmail = $senderEmail;
        $this->confirmationEmailTemplate = $confirmationEmailTemplate;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool true on success, false otherwise
     */
    public function sendAccountConfirmationEmail(UserInterface $user)
    {
        $from = $this->senderEmail;
        $to = $user->getUsername();
        $subject = '';
        $confirmationUrl = $this->urlGenerator->generate(
            'sherlockode_userconfirmation_set_password',
            [
                'confirmationToken' => $user->getConfirmationToken()
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $body = $this->templating->render(
            $this->confirmationEmailTemplate,
            [
                'user' => $user,
                'confirmationUrl' => $confirmationUrl
            ]
        );

        return $this->sendMessage($from, $to, $subject, $body);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $contentType
     *
     * @return bool true on success, false otherwise
     */
    private function sendMessage($from, $to, $subject, $body, $contentType = 'text/html')
    {
        $mail = \Swift_Message::newInstance();
        $mail
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType($contentType);

        return $this->mailer->send($mail) > 0;
    }
}
