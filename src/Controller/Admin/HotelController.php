<?php

namespace App\Controller\Admin;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/hotel")
 */
class HotelController extends AbstractController
{
    /**
     * @Route("/", name="admin_hotel_index", methods={"GET"})
     */
    public function index(HotelRepository $hotelRepository): Response
    {
        return $this->render('admin/hotel/index.html.twig', [
            'hotels' => $hotelRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_hotel_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
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
                    $hotel->setImage($fileName);
                }
                $entityManager->persist($hotel);
                $entityManager->flush();

                return $this->redirectToRoute('admin_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_hotel_show", methods={"GET"})
     */
    public function show(Hotel $hotel): Response
    {
        return $this->render('admin/hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_hotel_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
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
                    $hotel->setImage($fileName);
                }

                $entityManager->flush();

            return $this->redirectToRoute('admin_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }
    /**
     * @return string
     */
    private function generateUniqueFileName(){
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="admin_hotel_delete", methods={"POST"})
     */
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
