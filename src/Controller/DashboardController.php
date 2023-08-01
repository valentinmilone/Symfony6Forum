<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\Comentarios;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;



class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(PersistenceManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request)
    {
        // ...
        $user = $this->getUser();
        if ($user) {
            $em = $doctrine->getManager();
            $query = $em->getRepository(Posts::class)->buscarTodosLosPosts();
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /* page number */
                4 /* limit per page */
            );

            // Obtener los comentarios del usuario actual
            $comentarios = $em->getRepository(Comentarios::class)->findBy(['user' => $user]);

            return $this->render('dashboard/index.html.twig', [
                'pagination' => $pagination,
                'comentarios' => $comentarios,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}