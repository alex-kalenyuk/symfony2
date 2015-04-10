<?php

namespace TrainingBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use TrainingBundle\Entity\BlogComment;
use TrainingBundle\Entity\BlogPost;
use TrainingBundle\Form\BlogCommentType;
use TrainingBundle\Form\BlogPostType;
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
        $repository = $em->getRepository('TrainingBundle:BlogPost');

        return ['posts' => $repository->findAllOrderedByTime()];
    }

    /**
     * @Route("/create", name="blog_post_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $postForm = $this->createForm(
            new BlogPostType(),
            new BlogPost()
        );
        $postForm->handleRequest($request);

        if ($postForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($postForm->getData());
            $em->flush();

            $this->addFlash('notice', 'Post successfully added');
            return $this->redirectToRoute('blog_post_index');
        }

        return [
            'postForm' => $postForm->createView()
        ];
    }

    /**
     * @Route("/show/{id}", name="blog_post_show", requirements={"id": "\d+"})
     * @Template()
     */
    public function showAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em
            ->getRepository('TrainingBundle:BlogPost')
            ->findOneById($id);
        if (!$post) {
            throw $this->createNotFoundException('Post with id ' . $id . ' not found');
        }
        $commentEntity = new BlogComment();
        $commentEntity->setPost($post);
        $commentForm = $this->createForm(new BlogCommentType(), $commentEntity);

        $commentForm->handleRequest($request);

        if ($request->isXmlHttpRequest() && $commentForm->isValid()) {
            $em->persist($commentForm->getData());
            $em->flush();

            $this->pushNewComment($commentEntity->getDataArray());

            $html = $this->renderView(
                'TrainingBundle:BlogPost:comment.html.twig',
                ['comment' => $commentEntity->getDataArray()]
            );
            return new JsonResponse(['html'=>$html]);

//            $this->addFlash('notice', 'Your comment was added');
//            return $this->redirectToRoute('blog_post_show', ['id' => $id], 301);
        }
        $comments = $em->getRepository('TrainingBundle:BlogComment')->findByPost($post);

        return [
            'post' => $post,
            'comments' => $comments,
            'commentForm' => $commentForm->createView()
        ];
    }

    public function pushNewComment($data)
    {
        $html = $this->renderView('TrainingBundle:BlogPost:comment.html.twig', ['comment' => $data]);
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'comment pusher');
        $socket->connect("tcp://localhost:5555");
        $socket->send(json_encode(['html'=>$html]));
    }
}
