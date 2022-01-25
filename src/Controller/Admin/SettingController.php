<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/setting")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("/", name="setting_index", methods={"GET"})
     */
    public function index(SettingRepository $settingRepository): Response
    {
        return $this->render('admin/setting/index.html.twig', [
            'settings' => $settingRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="setting_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($setting);
            $entityManager->flush();

            return $this->redirectToRoute('setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/setting/new.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="setting_show", methods={"GET"})
     */
    public function show(Setting $setting): Response
    {
        return $this->render('admin/setting/show.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="setting_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/setting/edit.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="setting_delete", methods={"POST"})
     */
    public function delete(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$setting->getId(), $request->request->get('_token'))) {
            $entityManager->remove($setting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('setting_index', [], Response::HTTP_SEE_OTHER);
    }
}
