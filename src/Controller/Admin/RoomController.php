<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Room;
use App\Form\Admin\RoomType;
use App\Repository\Admin\RoomRepository;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/room")
 */
class RoomController extends AbstractController
{
    /**
     * @Route("/", name="admin_room_index", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('admin/room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="admin_room_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager,$id,HotelRepository $hotelRepository, RoomRepository $roomRepository): Response
    {
        $rooms=$roomRepository->findBy(['hotelid'=>$id]);
        $hotel=$hotelRepository->findBy(['id'=>$id]);
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
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
                $room->setImage($fileName);
            }
            $room->setHotelid($id);
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('admin_room_new', ['id'=> $id], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/room/new.html.twig', [
            'hotel' => $hotel,
            'room' => $room,
            'rooms' => $rooms,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_room_show", methods={"GET"})
     */
    public function show(Room $room): Response
    {
        return $this->render('admin/room/show.html.twig', [
            'room' => $room,
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName(){
        return md5(uniqid());
    }

    /**
     * @Route("/{id}/edit/{hid}", name="admin_room_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Room $room, EntityManagerInterface $entityManager, $hid): Response
    {
        $form = $this->createForm(RoomType::class, $room);
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
                $room->setImage($fileName);
            }
            $entityManager->flush();

            return $this->redirectToRoute('admin_room_new', ['id'=>$hid], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/{hid}", name="admin_room_delete", methods={"POST"})
     */
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager,$hid): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_room_new', ['id'=>$hid], Response::HTTP_SEE_OTHER);
    }
}
