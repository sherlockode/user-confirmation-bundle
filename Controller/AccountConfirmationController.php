<?php

namespace Sherlockode\UserConfirmationBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sherlockode\UserConfirmationBundle\Form\Type\ConfirmPasswordType;
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
    private $userManager;
    private $tokenStorage;

    /**
     * @param UserManagerInterface  $userManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(UserManagerInterface $userManager, TokenStorageInterface $tokenStorage)
    {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
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
            throw $this->createAccessDeniedException('Access denied');
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
}
