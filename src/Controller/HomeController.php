<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Repository\HotelRepository;
use App\Repository\SettingRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
        ]);
    }
    /**
     * @Route("/{id}", name="hotel_show", methods={"GET"})
     */
    public function show(Hotel $hotel): Response
    {
        return $this->render('home/hotelshow.html.twig', [
            'hotel' => $hotel,
        ]);
    }
}
