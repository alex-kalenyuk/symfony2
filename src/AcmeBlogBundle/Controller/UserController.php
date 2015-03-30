<?php

namespace AcmeBlogBundle\Controller;


use AcmeBlogBundle\Entity\User;
use AcmeBlogBundle\Form\LoginType;
use AcmeBlogBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $token = $this->get('form.csrf_provider')->generateCsrfToken('user_index');;

        return [
            'users' => $em->getRepository('AcmeBlogBundle:User')->findAll(),
            'token' => $token
        ];
    }
    /**
     * @Route("/login", name="login_route")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('blog_post_index');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/create", name="user_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $userForm = $this->createForm(
            new UserType(),
            new User(),
            ['validation_groups' => ['Default', 'create']]
        );
        $userForm->handleRequest($request);

        if ($userForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userForm->getData());
            $em->flush();

            $this->addFlash('notice', 'User successfully added');
            return $this->redirectToRoute('user_index');
        }

        return [
            'userForm' => $userForm->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="user_edit")
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AcmeBlogBundle:User')->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('User with id ' . $id . ' not found');
        }

        $userForm = $this->createForm(
            new UserType(),
            $user,
            ['validation_groups' => ['Default', 'update']]
        );
        $userForm->handleRequest($request);

        if ($userForm->isValid()) {
            $em->flush();

            $this->addFlash('notice', 'User successfully updated');
            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'AcmeBlogBundle:User:create.html.twig',
            ['userForm' => $userForm->createView()]
        );
    }

    /**
     * @Route("/delete/{id}/{token}", name="user_delete")
     */
    public function deleteAction($id, $token)
    {
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('user_index', $token)) {
            $this->addFlash('notice', 'Woops! Token invalid!');
        } else {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AcmeBlogBundle:User')->findOneById($id);
            if ($user) {
                $em->remove($user);
                $em->flush();
                $this->addFlash('notice', 'User was deleted successfully');
            } else {
                $this->addFlash('notice', 'User was not deleted');
            }
        }

        return $this->redirectToRoute('user_index');
    }
}
