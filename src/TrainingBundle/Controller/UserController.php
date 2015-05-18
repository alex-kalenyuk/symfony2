<?php

namespace TrainingBundle\Controller;

use TrainingBundle\Entity\User;
use TrainingBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use adLDAP\adLDAP;

class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $token = $this->get('form.csrf_provider')->generateCsrfToken('user_index');

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
        if ($adLdap->authenticate(
            $this->getUser()->getUsername(), 
            $this->getUser()->getPassword()
        )) {
            $userRepository = $this->getDoctrine()->getManager()->getRepository('TrainingBundle:User');
            $filter = "(&(objectClass=user)(samaccounttype=" . adLDAP::ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=*))";
            $fields = array("samaccountname", "displayname", "mail", "objectGUID", "objectSid", "userprincipalname", "title", "manager");
            $sr = ldap_search($adLdap->getLdapConnection(), $adLdap->getBaseDn(), $filter, $fields);
            $entries = ldap_get_entries($adLdap->getLdapConnection(), $sr);

            for ($i=0; $i<$entries["count"]; $i++) {
                $guid = $adLdap->utilities()->decodeGuid($entries[$i]["objectguid"][0]);
                if (!isset($entries[$i]["mail"])) {
                    $entries[$i]["mail"] = $entries[$i]["userprincipalname"];
                }       
                $title = isset($entries[$i]["title"]) 
                        ? $entries[$i]["title"][0]
                        : NULL;         
                $managerId = isset($entries[$i]["manager"]) 
                        ? $userRepository->findManagerGUID($adLdap, $entries[$i]["manager"][0])
                        : NULL;
                $this->getDoctrine()->getEntityManager()
                    ->getConnection()
                    ->executeQuery(
                        'INSERT INTO user(id, name, email, title, manager_id) 
                            VALUES(:id, :name, :email, :title, :manager_id)
                            ON DUPLICATE KEY UPDATE name=VALUES(name), title=VALUES(title), manager_id=VALUES(manager_id)',
                        [
                            'id' => $guid, 
                            'name' => $entries[$i]["displayname"][0],
                            'email' => $entries[$i]["mail"][0],
                            'title' => $title,
                            'manager_id' => $managerId,
                        ]
                    );
            }
            
            $this->addFlash('notice', 'Data was synchronized successfully');
        } else {
            $this->addFlash('notice', 'Synchronization failed');    
        }

        return $this->redirectToRoute('user_index');
    }
}
