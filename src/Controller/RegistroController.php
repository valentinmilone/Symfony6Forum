<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function create( Request $request, PersistenceManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            // $user-> setBaneado(false);
            // $user-> getRoles(['ROLE_USER']);
            $user-> setPassword($passwordHasher->hashPassword($user, $form['password']->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('exito', message: User::REGISTRO_EXITOSO);
            return $this->redirectToRoute('registro');
        }

        return $this->render('registro/index.html.twig', [
            'controller_name' => 'RegistroController',
            'formulario' => $form->createView(),
        ]);
    }

}
