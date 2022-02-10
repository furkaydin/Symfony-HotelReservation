<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\Admin\Reservation;
use App\Entity\User;
use App\Form\Admin\CommentType;
use App\Form\Admin\ReservationType;
use App\Form\UserType;
use App\Repository\Admin\ReservationRepository;
use App\Repository\Admin\RoomRepository;
use App\Repository\HotelRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');


    }

    /**
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImage($fileName);
            }
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(): Response
    {
        return $this->render('user/comments.html.twig');


    }
    /**
     * @Route("/hotels", name="user_hotels", methods={"GET"})
     */
    public function hotels(): Response
    {
        return $this->render('user/hotels.html.twig');


    }
    /**
     * @Route("/rezervations", name="user_rezervations", methods={"GET"})
     */
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);


        return $this->render('user/rezervations.html.twig', [
            'reservations' =>$reservations,

        ]);
    }




    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user,$id, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImage($fileName);
            }
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }




    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET", "POST"})
     */
    public function newcomment(Request $request, EntityManagerInterface $entityManager,$id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token') ;

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('comment',$submittedToken)) {

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setHotelid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Your comment has been sent successfuly');

                return $this->redirectToRoute('hotel_show', ['id' => $id], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('hotel_show', ['id'=> $id],Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/reservation/{hid}/{rid}", name="user_reservation_new", methods={"GET","POST"})
     */
    public function newreservation(Request $request,$hid,$rid,HotelRepository $hotelRepository, RoomRepository $roomRepository,EntityManagerInterface $entityManager): Response
    {
        $days=$_REQUEST["days"];
        $hotel=$hotelRepository->findOneBy(['id'=>$hid]);
        $room=$roomRepository->findOneBy(['id'=>$rid]);
        $total=$days * $room->getPrice();



        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);


        $submittedToken = $request->request->get('token');
        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-reservation', $submittedToken)) {


                $reservation->setStatus('New');
                $reservation->setIp($_SERVER['REMOTE_ADDR']);
                $reservation->setHotelid($hid);
                $reservation->setRoomid($rid);
                $user = $this->getUser(); // Get login User data
                $reservation->setUserid($user->getId());
                $reservation->setDays($days);
                $reservation->setTotal($total);

                $entityManager->persist($reservation);
                $entityManager->flush();

                return $this->redirectToRoute('user_rezervations');
            }
        }


        return $this->render('user/newreservation.html.twig', [
            'reservation' => $reservation,
            'room' => $room,
            'hotel' => $hotel,
            'total' => $total,
            'days' => $days,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


}
