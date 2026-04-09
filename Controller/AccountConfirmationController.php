<?php

namespace Sherlockode\UserConfirmationBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sherlockode\UserConfirmationBundle\Event\UnknownTokenEvent;
use Sherlockode\UserConfirmationBundle\Form\Type\ConfirmPasswordType;
use Sherlockode\UserConfirmationBundle\Manager\MailManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Class AccountConfirmationController
 */
class AccountConfirmationController
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var MailManagerInterface
     */
    private $mailManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $redirectionRoute;

    /**
     * @var string
     */
    private $confirmationFormTemplate;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UserManagerInterface     $userManager
     * @param TokenStorageInterface    $tokenStorage
     * @param TokenGeneratorInterface  $tokenGenerator
     * @param MailManagerInterface     $mailManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $redirectionRoute
     * @param string                   $confirmationFormTemplate
     * @param FormFactoryInterface     $formFactory
     * @param TwigEnvironment          $twig
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage,
        TokenGeneratorInterface $tokenGenerator,
        MailManagerInterface $mailManager,
        EventDispatcherInterface $eventDispatcher,
        string $redirectionRoute,
        string $confirmationFormTemplate,
        FormFactoryInterface $formFactory,
        TwigEnvironment $twig,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailManager = $mailManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->redirectionRoute = $redirectionRoute;
        $this->confirmationFormTemplate = $confirmationFormTemplate;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/registration/{confirmationToken}', name: 'sherlockode_user_confirmation_set_password')]
    public function setPasswordAction(
        Request $request,
        $confirmationToken
    ) {
        $user = $this->userManager->findUserByConfirmationToken($confirmationToken);
        if (!$user instanceof UserInterface) {
            $event = new UnknownTokenEvent($confirmationToken);
            if (Kernel::VERSION_ID < 40300) {
                $this->eventDispatcher->dispatch(UnknownTokenEvent::class, $event);
            } else {
                $this->eventDispatcher->dispatch($event, UnknownTokenEvent::class);
            }

            if ($event->getResponse()) {
                return $event->getResponse();
            }

            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(ConfirmPasswordType::class, $user, [
            'data_class' => $this->userManager->getClass(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            $usernamePasswordToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($usernamePasswordToken);

            $url = $this->urlGenerator->generate($this->redirectionRoute);
            $response = new RedirectResponse($url);

            $event = new FilterUserResponseEvent($user, $request, $response);
            if (Kernel::VERSION_ID < 40300) {
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, $event);
            } else {
                $this->eventDispatcher->dispatch($event, FOSUserEvents::REGISTRATION_CONFIRMED);
            }

            return $response;
        }

        return new Response($this->twig->render('@SherlockodeUserConfirmation/Form/confirmation_content.html.twig', [
            'form' => $form->createView(),
            'parentTemplate' => $this->confirmationFormTemplate,
        ]));
    }

    #[Route('/send-confirmation/{id}', name: 'sherlockode_user_confirmation_send_confirmation')]
    public function sendConfirmationEmailAction(Request $request, $id)
    {
        $user = $this->userManager->findUserBy(['id' => $id]);
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedHttpException();
        }

        $referer = $request->server->get('HTTP_REFERER');

        if ($user->isEnabled()) {
            return new RedirectResponse($referer);
        }

        if ($user->getConfirmationToken() === null) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $this->userManager->updateUser($user);
        }

        $this->mailManager->sendAccountConfirmationEmail($user);

        return new RedirectResponse($referer);
    }
}
