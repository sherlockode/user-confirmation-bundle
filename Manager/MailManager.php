<?php

namespace Sherlockode\UserConfirmationBundle\Manager;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

class MailManager implements MailManagerInterface
{
    /**
     * @var MailerInterface
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
     * @param MailerInterface       $mailer
     * @param TwigEnvironment       $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface   $translator
     * @param string                $senderEmail
     * @param string                $confirmationEmailTemplate
     * @param string                $emailSubject
     */
    public function __construct(
        MailerInterface $mailer,
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
     *
     * @return bool true on success, false otherwise
     */
    private function sendMessage($from, $to, $subject, $body)
    {
        $mail = new Email();
        $mail
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($body);

        try {
            $this->mailer->send($mail);
        } catch (TransportExceptionInterface $exception) {
            return false;
        }

        return true;
    }
}
