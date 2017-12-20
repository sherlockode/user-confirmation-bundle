<?php

namespace Sherlockode\UserConfirmationBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
    /**
     * @Route("/registration/{confirmationToken}", name="sherlockode_userconfirmation_set_password")
     *
     * @param Request               $request
     * @param UserManager           $userManager
     * @param TokenStorageInterface $tokenStorage
     * @param string                $confirmationToken
     *
     * @return Response
     */
    public function setPasswordAction(
        Request $request,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        $confirmationToken
    ) {
        $user = $userManager->findUserBy(['confirmationToken' => $confirmationToken]);
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $form = $this->createForm(ConfirmPasswordType::class, ['_confirmationToken' => $confirmationToken]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $confirmationToken = $form->getData()['_confirmationToken'];
            $user = $userManager->findUserBy(['confirmationToken' => $confirmationToken]);

            if (!$user instanceof UserInterface) {
                throw $this->createAccessDeniedException('Access denied');
            }

            $user->setPlainPassword($form->getData()['password']);
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $userManager->updateUser($user);
            $usernamePasswordToken = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $tokenStorage->setToken($usernamePasswordToken);

            return $this->redirectToRoute(
                $this->getParameter('sherlockode_user_confirmation.redirect_after_confirmation')
            );
        }

        return $this->render(
            'SherlockodeUserConfirmationBundle:Form:confirmation.html.twig',
            [
                'form' => $form->createView(),
                'parentTemplate' => $this->getParameter('sherlockode_user_confirmation.templates.confirmation_form'),
            ]
        );
    }
}
