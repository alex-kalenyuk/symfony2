<?php

namespace AcmeBlogBundle\Controller;

use AcmeBlogBundle\Entity\BlogComment;
use AcmeBlogBundle\Entity\BlogPost;
use AcmeBlogBundle\Form\BlogCommentType;
use AcmeBlogBundle\Form\BlogPostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BlogPostController extends Controller
{
    /**
     * @Route("/", name="blog_post_index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AcmeBlogBundle:BlogPost');

        return ['posts' => $repository->findAllOrderedByTime()];
    }

    /**
     * @Route("/show/{id}", name="blog_post_show", requirements={"id": "\d+"})
     * @Template()
     */
    public function showAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em
            ->getRepository('AcmeBlogBundle:BlogPost')
            ->findOneById($id);
        if (!$post) {
            throw $this->createNotFoundException('Post with id ' . $id . ' not found');
        }
        $commentEntity = new BlogComment();
        $commentEntity->setPost($post);
        $commentForm = $this->createForm(new BlogCommentType(), $commentEntity);

        $commentForm->handleRequest($request);

        if ($commentForm->isValid()) {
            $em->persist($commentForm->getData());
            $em->flush();

            $this->addFlash('notice', 'Your comment was added');
            return $this->redirectToRoute('blog_post_show', ['id' => $id], 301);
        }
        $comments = $em->getRepository('AcmeBlogBundle:BlogComment')->findByPost($post);

        return [
            'post' => $post,
            'comments' => $comments,
            'commentForm' => $commentForm->createView()
        ];
    }
}
