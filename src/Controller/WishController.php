<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/detail/{id}', name: 'detail', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function detail(Wish $wish, WishRepository $repository): Response
    {
        return $this->render('wish/detail.html.twig', [
            'wish' => $wish
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager,Request $request): Response
    {
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()) {
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
    public function edit(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
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


    #[Route('/delete/{id}', name: 'delete', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function delete(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wish->getId(), $request->request->get('_token'))) {
            $entityManager->remove($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Le Souhait "' . $wish->getTitle() . '" a été supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');
        }

        return $this->redirectToRoute('wish_list');
    }
}
