<?php

namespace Sherlockode\UserConfirmationBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sherlockode\UserConfirmationBundle\Form\Type\ConfirmPasswordType;
use Sherlockode\UserConfirmationBundle\Manager\MailManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AccountConfirmationController
 */
class AccountConfirmationController extends AbstractController
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
     * @var string
     */
    private $redirectionRoute;

    /**
     * @var string
     */
    private $confirmationFormTemplate;

    /**
     * @param UserManagerInterface    $userManager
     * @param TokenStorageInterface   $tokenStorage
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailManagerInterface    $mailManager
     * @param string                  $redirectionRoute
     * @param string                  $confirmationFormTemplate
     */
    public function __construct(
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage,
        TokenGeneratorInterface $tokenGenerator,
        MailManagerInterface $mailManager,
        $redirectionRoute,
        $confirmationFormTemplate
    ) {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailManager = $mailManager;
        $this->redirectionRoute = $redirectionRoute;
        $this->confirmationFormTemplate = $confirmationFormTemplate;
    }

    /**
     * @param Request $request
     * @param string  $confirmationToken
     *
     * @return Response
     */
    public function setPasswordAction(
        Request $request,
        $confirmationToken
    ) {
        $user = $this->userManager->findUserByConfirmationToken($confirmationToken);
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ConfirmPasswordType::class, $user, [
            'data_class' => $this->userManager->getClass(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            $usernamePasswordToken = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($usernamePasswordToken);

            return $this->redirectToRoute($this->redirectionRoute);
        }

        return $this->render('@SherlockodeUserConfirmation/Form/confirmation_content.html.twig', [
            'form' => $form->createView(),
            'parentTemplate' => $this->confirmationFormTemplate,
        ]);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function sendConfirmationEmailAction(Request $request, $id)
    {
        $user = $this->userManager->findUserBy(['id' => $id]);
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $referer = $request->server->get('HTTP_REFERER');

        if ($user->isEnabled()) {
            return $this->redirect($referer);
        }

        if ($user->getConfirmationToken() === null) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $this->userManager->updateUser($user);
        }

        $this->mailManager->sendAccountConfirmationEmail($user);

        return $this->redirect($referer);
    }
}
