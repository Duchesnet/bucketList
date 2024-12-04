<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Wish;
use App\Form\CommentType;
use App\Form\WishType;
use App\Helpers\Censurator;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wish', name: 'wish_')]
class WishController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(WishRepository $repository): Response
    {

        $wishes = $repository->findBy(['isPublished' => true], ['dateCreated' => 'DESC']);

        return $this->render('wish/list.html.twig', [
            'wishes' => $wishes
        ]);
    }

    #[Route('/detail/{id}', name: 'detail', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function detail(Wish $wish, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setAuthor($this->getUser()->getUsername());
            $comment->setDateCreated(new \DateTime('now'));
            $comment->setWish($wish);

            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès');
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }



        return $this->render('wish/detail.html.twig', [
            'wish' => $wish,
            'comments' => $wish->getComments(),
            'commentForm' => $commentForm,

        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager,Censurator $censurator ,Request $request): Response
    {
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()) {
            $censorTitle = $censurator->purify($wish->getTitle());
            $wish->setTitle($censorTitle);
            $censorDesciption = $censurator->purify($wish->getDescription());
            $wish->setDescription($censorDesciption);
            $wish->setAuthor($this->getUser()->getUsername());
            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Le Souhait ' . $wish->getTitle() . ' a été créé avec succès !');
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        return $this->render('wish/add.html.twig', [
            'wishForm' => $wishForm
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('WISH_EDIT', 'wish')]
    public function edit(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
//        if ($wish->getAuthor() !== $this->getUser()) {
//            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres souhaits');
//            throw new AccessDeniedException('Vous ne pouvez modifier que vos propres souhaits.');
//        }

        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le Souhait "' . $wish->getTitle() . '" a été modifié avec succès !');
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        return $this->render('wish/add.html.twig', [
            'wishForm' => $wishForm,
            'wish' => $wish,
        ]);
    }


    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $wish->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');
            return $this->redirectToRoute('wish_list');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Le Souhait "' . $wish->getTitle() . '" a été supprimé avec succès par un administrateur.');
            return $this->redirectToRoute('wish_list');
        }

        if ($wish->getAuthor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos souhaits');
            throw new AccessDeniedException('Vous ne pouvez supprimer que vos propres souhaits.');
        }

        $entityManager->remove($wish);
        $entityManager->flush();

        $this->addFlash('success', 'Le Souhait "' . $wish->getTitle() . '" a été supprimé avec succès !');
        return $this->redirectToRoute('wish_list');
    }
}
