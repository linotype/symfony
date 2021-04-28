<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeUser;
use Linotype\Bundle\LinotypeBundle\Security\LinotypeAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LinotypeSecurityController extends AbstractController
{
    /**
     * @Route("/login", name="linotype_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
		
        return $this->render('@Linotype/Security/login.twig', [
			'last_username' => $lastUsername, 
			'error' => $error
		]);
    }

    /**
     * @Route("/logout", name="linotype_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
	 * @Route("/signin", name="linotype_signin")
	 */
	public function signin( Request $request, UserPasswordEncoderInterface $userPasswordEncoder, GuardAuthenticatorHandler $guardAuthenticatorHandler, LinotypeAuthenticator $linotypeAuthenticator )
	{
		
		if ( $request->isMethod('POST') &&
			 $request->request->get('username') &&
			 $request->request->get('email') &&
			 $request->request->get('password') ) {

			$user = new LinotypeUser();
			$user->setUsername($request->request->get('username'));
			$user->setEmail($request->request->get('email'));
			$user->setPassword( $userPasswordEncoder->encodePassword(
				$user,
				$request->request->get('password')
			));
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			return $guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
				$user,
				$request,
				$linotypeAuthenticator,
				'main'
			);

		}

		$error = false;
		if ( $request->request->has('password') && $request->request->get('password') == "" ) {
			$error = 'Require password.';
		}
		if ( $request->request->has('email') && $request->request->get('email') == "" ) {
			$error = 'Require email.';
		}
		if ( $request->request->has('username') && $request->request->get('username') == "" ) {
			$error = 'Require username.';
		}

		$last_username = '';
		$last_email = '';
		if ( $request->request->has('username') && $request->request->get('username') ) {
			$last_username = $request->request->get('username');
		}
		if ( $request->request->has('email') && $request->request->get('email') ) {
			$last_email = $request->request->get('email');
		}
		
		return $this->render('@Linotype/Security/signin.twig',[
			'error' => $error,
			'last_username' => $last_username,
			'last_email' => $last_email,
		]);
	}

}
