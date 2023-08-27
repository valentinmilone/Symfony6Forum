<?php

namespace App\Controller;
//use App\Repository;

use App\Entity\Posts;
use App\Entity\Comentarios;
use App\Form\PostsType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;







class PostsController extends AbstractController
{
    #[Route('/registrar-posts', name: 'RegistrarPosts')]
    public function index(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger)//: Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('foto')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new Exception("ups, ha ocurrido un error");
                     
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setFoto($newFilename);
            }
            /** @var User $user */
            $user = $this->getUser();
            $post->setUser($user);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('posts/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/post/{id}', name: 'VerPosts')]
        public function VerPost($id,PersistenceManagerRegistry $doctrine){
            $user = $this->getUser();
            $entityManager = $doctrine->getManager();
            $post = $entityManager->getRepository(Posts::class)->find($id);
            $comentarios = $entityManager->getRepository(Comentarios::class)->findBy(['posts' => $post]);
            $likesDeEstePost = explode(',', $post->getLikes()); // Convertir los likes en un array
            return $this->render('posts/verPost.html.twig',[
                'post'=>$post,
                'likesDeEstePost' => $likesDeEstePost,
                'comentarios'=> $comentarios]);
        }

        #[Route('/mis-posts', name: 'MisPosts')]
        public function MisPosts(PersistenceManagerRegistry $doctrine){
            $entityManager = $doctrine->getManager();
            $user = $this->getUser();
            $user = $this->getUser();
            $posts = $entityManager->getRepository(Posts::class)->findBy(['user'=>$user]);
            return $this->render('posts/MisPosts.html.twig',['posts'=>$posts]);
        }
         
        #[Route('/Likes', options: ['expose' => true], name: 'Likes')]
        public function Like(Request $request, PersistenceManagerRegistry $doctrine){
            if($request->isXmlHttpRequest()){
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $id = $request->request->get('id');
                $post = $em->getRepository(Posts::class)->find($id);
                $likes = $post->getLikes();
                $likes .= $user->getId().',';
                $post->setLikes($likes);
                $em->flush();
                return new JsonResponse(['likes'=>$likes]);
            }else{
                throw new \Exception('Estás tratando de hackearme?');
            }
        }
}