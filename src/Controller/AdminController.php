<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    
    #[Route('/admin/post/create', name:'create_post')]
    #[isGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser())
                ->setCreatedAt(new DateTime());

            $this->addFlash('success', 'Votre article a été créé avec succès. il ne sera visible que lorsque la case publié sera cochée.');
            
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('show', ['id' => $post->getId()]);
        }
        
        return $this->render('admin/create.html.twig', [
            'postForm' => $form
        ]);
    }

    #[Route('/admin/post/edit/{id<[0-9]+>}', name:'edit_post')]
    #[isGranted('ROLE_ADMIN')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em, PostRepository $postRepository): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre article a été modifié avec succès. il ne sera visible que lorsque la case publié sera cochée.');
            
            $postRepository->save($post);
            $em->flush();

            return $this->redirectToRoute('show', ['id' => $post->getId()]);
        }
        
        return $this->render('admin/edit.html.twig', [
            'postForm' => $form
        ]);
    }
}
