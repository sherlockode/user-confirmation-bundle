<?php

namespace Sherlockode\UserConfirmationBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sherlockode\UserConfirmationBundle\Form\Type\ConfirmPasswordType;
use Sherlockode\UserConfirmationBundle\Manager\MailManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AccountConfirmationController
 */
class AccountConfirmationController extends Controller
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
     * @param UserManagerInterface    $userManager
     * @param TokenStorageInterface   $tokenStorage
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailManagerInterface    $mailManager
     */
    public function __construct(
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage,
        TokenGeneratorInterface $tokenGenerator,
        MailManagerInterface $mailManager
    ) {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailManager = $mailManager;
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

        $form = $this->createForm(ConfirmPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPlainPassword($form->getData()['password']);
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            $usernamePasswordToken = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($usernamePasswordToken);

            return $this->redirectToRoute(
                $this->getParameter('sherlockode_user_confirmation.redirect_after_confirmation')
            );
        }

        return $this->render('@SherlockodeUserConfirmation/Form/confirmation_content.html.twig', [
            'form' => $form->createView(),
            'parentTemplate' => $this->getParameter('sherlockode_user_confirmation.templates.confirmation_form'),
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
