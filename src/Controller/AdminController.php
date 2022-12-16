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

#[Route('/admin')]
#[isGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'admin')]
    // #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Accès refuser aux nom-admins')]
    public function index(): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "accès refusé");

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

    #[Route('/post/delete/{id<[0-9]+>}', name: 'delete_post')]
    public function delete(Post $post, Request $request): Response
    {
        if ($this->checkToken($post, $request)) {
            $this->addFlash('success', 'L\'article a été supprimé avec succès.');

            $this->postRepository->remove($post, true);
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/post/publish/{id<[0-9]+>}', name: 'publish_post')]
    public function publish(Post $post, Request $request): Response
    {
        if ($this->checkToken($post, $request)) {
            $post->setIsPublished(!$post->isIsPublished());

            $this->postRepository->save($post, true);
        }

        return $this->redirectToRoute('admin');
    }

    private function checkToken(Post $post, Request $request): bool
    {
        $token = $request->query->get('token');

        return $this->isCsrfTokenValid('publish' . $post->getId(), $token);
    }


    #[Route('/post/create', name: 'create_post')]
    public function create(Request $request): Response
    {
        $post = new Post;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser())
                ->setCreatedAt(new DateTime());

            $this->addFlash('success', 'Votre article a été créé avec succès. il ne sera visible que lorsque la case publié sera cochée.');

            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('show', ['id' => $post->getId()]);
        }

        return $this->render('admin/create.html.twig', [
            'postForm' => $form,
            'action' => 'create'
        ]);
    }

    #[Route('/post/edit/{id<[0-9]+>}', name: 'edit_post')]
    public function edit(Post $post, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre article a été modifié avec succès. il ne sera visible que lorsque la case publié sera cochée.');

            $this->postRepository->save($post);
            $this->em->flush();

            return $this->redirectToRoute('show', ['id' => $post->getId()]);
        }

        return $this->render('admin/create.html.twig', [
            'postForm' => $form,
        ]);
    }
}
