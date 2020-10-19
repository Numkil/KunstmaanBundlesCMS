<?php

namespace Kunstmaan\AdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Kunstmaan\AdminBundle\Event\ChangePasswordSuccessEvent;
use Kunstmaan\AdminBundle\Event\Events;
use Kunstmaan\AdminBundle\Form\NewPasswordType;
use Kunstmaan\AdminBundle\Form\PasswordRequestType;
use Kunstmaan\AdminBundle\Service\PasswordMailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResettingController extends AbstractController
{
    /** @var string */
    private $userClass;

    /** @var PasswordMailerInterface */
    private $passwordMailer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SessionInterface */
    private $session;

    public function __construct(
        string $userClass,
        PasswordMailerInterface $passwordMailer,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ) {
        $this->userClass = $userClass;
        $this->passwordMailer = $passwordMailer;
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    /**
     * @Route("/resetting/request", name="cms_reset_password", methods={"GET", "POST"})
     * @Route("/resetting/request", name="fos_user_resetting_request", methods={"GET", "POST"})
     */
    public function resetPasswordAction(Request $request)
    {
        $form = $this->createForm(PasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $token = bin2hex(random_bytes(32));
            $user = $this->em->getRepository($this->userClass)->findOneBy(['email' => $email]);
            if (!$user instanceof $this->userClass) {
                $user = $this->em->getRepository($this->userClass)->findOneBy(['username' => $email]);
            }

            if ($user instanceof $this->userClass) {
                $user->setConfirmationToken($token);
                $this->em->flush();
                $this->passwordMailer->sendPasswordForgotMail($user, $request->getLocale());
                $this->addFlash('success', 'security.resetting.send_email_success');

                return $this->redirectToRoute('cms_reset_password');
            }
        }

        return $this->render('@KunstmaanAdmin/Security/reset-password.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/reset_password/confirm/{token}", name="cms_reset_password_confirm", methods={"GET", "POST"})
     */
    public function resetPasswordCheckAction(
        Request $request,
        string $token
    ) {
        $user = $this->em->getRepository($this->userClass)->findOneBy(['confirmationToken' => $token]);

        if (!$token || !$user instanceof $this->userClass) {
            $this->addFlash('danger', 'security.resetting.user_not_found');

            return $this->redirectToRoute('cms_reset_password');
        }

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $password = $this->encoder->encodePassword($user, $plainPassword);
            $user->setPassword($password);
            $user->setConfirmationToken(null);
            $this->em->flush();

            $token = new UsernamePasswordToken($user, $password, 'main');
            $this->tokenStorage->setToken($token);
            $this->session->set('_security_main', serialize($token));

            $url = $this->generateUrl('KunstmaanAdminBundle_homepage');
            $response = new RedirectResponse($url);

            $this->dispatch(new ChangePasswordSuccessEvent($user, $request, $response), Events::CHANGE_PASSWORD_COMPLETED);

            $this->addFlash('success', 'security.resetting.password_set_success');

            return $response;
        }

        return $this->render('@KunstmaanAdmin/Security/reset-password-confirm.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param object $event
     * @param string $eventName
     *
     * @return object
     */
    private function dispatch($event, string $eventName)
    {
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $eventDispatcher = LegacyEventDispatcherProxy::decorate($this->eventDispatcher);

            return $eventDispatcher->dispatch($event, $eventName);
        }

        return $this->eventDispatcher->dispatch($eventName, $event);
    }
}
