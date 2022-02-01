<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Hotel;
use App\Entity\Setting;
use App\Form\Admin\MessagesType;
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
        $slider=$hotelRepository->findBy(array(),array(),3);
        $hotels=$hotelRepository->findBy(array(),array(),4);
        $newhotels=$hotelRepository->findBy(array(),array(),10);

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
    public function show(Hotel $hotel,$id, ImageRepository $imageRepository): Response
    {
        $images=$imageRepository->findBy(['hotel'=>$id]);

        return $this->render('home/hotelshow.html.twig', [
            'hotel' => $hotel,
            'images' => $images,
        ]);
    }

}
