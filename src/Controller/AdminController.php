<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[isGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[Route('/admin', name: 'admin')]
    // #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Accès refuser aux nom-admins')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "accès refusé");
        
        // if(!$this->getUser()) {
        //     $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');

        //     return $this->redirectToRoute('home');
        // }

        // if(!$this->isGranted('ROLE_ADMIN')) {
        //     $this->addFlash('danger', 'Vous devez être admin pour accéder à cette page');

        //     return $this->redirectToRoute('home');
        // }



        return $this->render('admin/index.html.twig', [
            'posts' => $this->postRepository->findAll()
        ]);
    }

    #[Route('/admin/post/delete/{id<[0-9]+>}', name: 'delete_post')]
    #[isGranted('ROLE_ADMIN')]
    public function delete(Post $post, Request $request): Response
    {
        $token = $request->query->get('token');

        if($this->isCsrfTokenValid('deletePost' . $post->getId(), $token)) {
            $this->addFlash('success', 'L\'article a été supprimé avec succès.');

            $this->postRepository->remove($post, true);
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/post/publis/{id<[0-9]+>}', name: 'publish_post')]
    #[isGranted('ROLE_ADMIN')]
    public function publish(Post $post, Request $request): Response
    {
        $token = $request->query->get('token');

        if($this->isCsrfTokenValid('publish' . $post->getId(), $token)) {
            $post->setIsPublished(!$post->isIsPublished());

            $this->postRepository->save($post, true);
        }

        return $this->redirectToRoute('admin');
    }
}
