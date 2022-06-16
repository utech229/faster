<?php

namespace App\Controller;

use App\Entity\ContactGroup;
use App\Form\ContactGroupType;
use App\Repository\ContactGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact/group')]
class ContactGroupController extends AbstractController
{
    #[Route('/', name: 'app_contact_group_index', methods: ['GET'])]
    public function index(ContactGroupRepository $contactGroupRepository): Response
    {
        return $this->render('contact_group/index.html.twig', [
            'contact_groups' => $contactGroupRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contact_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContactGroupRepository $contactGroupRepository): Response
    {
        $contactGroup = new ContactGroup();
        $form = $this->createForm(ContactGroupType::class, $contactGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactGroupRepository->add($contactGroup, true);

            return $this->redirectToRoute('app_contact_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact_group/new.html.twig', [
            'contact_group' => $contactGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_group_show', methods: ['GET'])]
    public function show(ContactGroup $contactGroup): Response
    {
        return $this->render('contact_group/show.html.twig', [
            'contact_group' => $contactGroup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactGroup $contactGroup, ContactGroupRepository $contactGroupRepository): Response
    {
        $form = $this->createForm(ContactGroupType::class, $contactGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactGroupRepository->add($contactGroup, true);

            return $this->redirectToRoute('app_contact_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact_group/edit.html.twig', [
            'contact_group' => $contactGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_group_delete', methods: ['POST'])]
    public function delete(Request $request, ContactGroup $contactGroup, ContactGroupRepository $contactGroupRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contactGroup->getId(), $request->request->get('_token'))) {
            $contactGroupRepository->remove($contactGroup, true);
        }

        return $this->redirectToRoute('app_contact_group_index', [], Response::HTTP_SEE_OTHER);
    }
}
