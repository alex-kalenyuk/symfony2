<?php

namespace TrainingBundle\Controller;

use TrainingBundle\Entity\User;
use TrainingBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            'users' => $em->getRepository('TrainingBundle:User')->findAll(),
            'token' => $token
        ];
    }
    /**
     * @Route("/login", name="login_route")
     * @Template()
     */
    public function loginAction(Request $request)
    {
//        $ldap = ldap_connect("levi9.com", 389);
//        if ($bind = ldap_bind($ldap, 'o.kaleniuk@levi9.com', 'myDate0608')) {
//            //die('strange');
//            ldap_set_option ($ldap, LDAP_OPT_REFERRALS, 0);
//            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
//            $sr = ldap_search($ldap, "ou=Levi9,dc=levi9,dc=com", "(&(objectClass=user)(objectCategory=person)(samaccountname=o.kaleniuk))", ["cn", "dn"]);
//            $info = ldap_get_entries($ldap, $sr);
//            var_dump($info);
//          die('logged in');
//        } else {
//          die('fail');
//        }
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
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
        $user = $em->getRepository('TrainingBundle:User')->findOneById($id);
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
            'TrainingBundle:User:create.html.twig',
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
            $user = $em->getRepository('TrainingBundle:User')->findOneById($id);
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

    /**
     * @Route("/sync", name="user_create")
     */
    public function syncAction(Request $request)
    {
        $adLdap = $this->get('ztec.security.active.directory.service.adldap')->getInstance();
        $authProvider = $this->get('security.authentication.provider.ztec.active_directory.default');
//        $token = $this->container->get('security.token_storage');
//        $credentials = $token->getCredentials();
//        $isAD = $adLdap->authenticate(
//            $this->getUser()->getUsername(), 
//            "myDate0608"
//        );
        $users = $adLdap->user()->all();
        
        if (1==1) {
            $this->addFlash('notice', 'Data was synchronized successfully');
        } else {
            $this->addFlash('notice', 'Synchronization failed');
        }

        return $this->redirectToRoute('user_index');
    }
}
