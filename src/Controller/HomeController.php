<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Hotel;
use App\Entity\Setting;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\Admin\RoomRepository;
use App\Repository\HotelRepository;
use App\Repository\ImageRepository;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, HotelRepository $hotelRepository): Response
    {
        $setting=$settingRepository->findAll();
        $slider=$hotelRepository->findBy(['status'=>'True'],['title'=>'ASC'] ,3);
        $hotels=$hotelRepository->findBy(['status'=>'True'],['title'=>'DESC'] ,4);
        $newhotels=$hotelRepository->findBy(['status'=>'True'],['title'=>'DESC'] ,10);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'hotels'=>$hotels,
            'newhotels'=>$newhotels,
        ]);
    }
    /**
     * @Route("/about", name="home_about")
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/contact", name="home_contact",methods={"GET", "POST"})
     */
    public function contact(SettingRepository $settingRepository,Request $request, EntityManagerInterface $entityManager): Response
    {

        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token') ;

        $setting=$settingRepository->findAll();

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)) {
                $message->setStatuss('New');


                $entityManager->persist($message);
                $entityManager->flush();



                return $this->redirectToRoute('home_contact', [], Response::HTTP_SEE_OTHER);
            }
        }


        return $this->render('home/contact.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
            'message'=>$message,
        ]);
    }
    /**
     * @Route("/{id}", name="hotel_show", methods={"GET"})
     */
    public function show(Hotel $hotel,$id, ImageRepository $imageRepository, CommentRepository $commentRepository, RoomRepository $roomRepository): Response
    {
        $images=$imageRepository->findBy(['hotel'=>$id]);
        $comments=$commentRepository->findBy(['hotelid'=>$id, 'status'=>'True']);
        $rooms =$roomRepository->findBy(['hotelid'=>$id, 'status'=>'True']);

        return $this->render('home/hotelshow.html.twig', [
            'hotel' => $hotel,
            'images' => $images,
            'rooms' => $rooms,
            'comments' => $comments,
        ]);
    }

}
