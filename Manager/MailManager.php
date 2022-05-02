<?php

namespace Sherlockode\UserConfirmationBundle\Manager;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

class MailManager implements MailManagerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $confirmationEmailTemplate;

    /**
     * @var string
     */
    private $emailSubject;

    /**
     * MailManager constructor.
     *
     * @param \Swift_Mailer         $mailer
     * @param TwigEnvironment       $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface   $translator
     * @param string                $senderEmail
     * @param string                $confirmationEmailTemplate
     * @param string                $emailSubject
     */
    public function __construct(
        \Swift_Mailer $mailer,
        TwigEnvironment $twig,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        $senderEmail,
        $confirmationEmailTemplate,
        $emailSubject
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->senderEmail = $senderEmail;
        $this->confirmationEmailTemplate = $confirmationEmailTemplate;
        $this->emailSubject = $emailSubject;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool true on success, false otherwise
     */
    public function sendAccountConfirmationEmail(UserInterface $user)
    {
        if (!$user->getConfirmationToken()) {
            return false;
        }

        $subject = $this->translator->trans($this->emailSubject, [], 'SherlockodeUserConfirmationBundle');
        $confirmationUrl = $this->urlGenerator->generate(
            'sherlockode_user_confirmation_set_password',
            [
                'confirmationToken' => $user->getConfirmationToken()
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $body = $this->twig->render(
            $this->confirmationEmailTemplate,
            [
                'user' => $user,
                'confirmationUrl' => $confirmationUrl
            ]
        );

        return $this->sendMessage($this->senderEmail, $user->getEmail(), $subject, $body);
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
        $mail = new \Swift_Message();
        $mail
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType($contentType);

        return $this->mailer->send($mail) > 0;
    }
}
