<?php

namespace App\Controller;

use App\Entity\ContactGroupField;
use App\Form\ContactGroupFieldType;
use App\Repository\ContactGroupFieldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact/group/field')]
class ContactGroupFieldController extends AbstractController
{
    #[Route('/', name: 'app_contact_group_field_index', methods: ['GET'])]
    public function index(ContactGroupFieldRepository $contactGroupFieldRepository): Response
    {
        return $this->render('contact_group_field/index.html.twig', [
            'contact_group_fields' => $contactGroupFieldRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contact_group_field_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContactGroupFieldRepository $contactGroupFieldRepository): Response
    {
        $contactGroupField = new ContactGroupField();
        $form = $this->createForm(ContactGroupFieldType::class, $contactGroupField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactGroupFieldRepository->add($contactGroupField, true);

            return $this->redirectToRoute('app_contact_group_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact_group_field/new.html.twig', [
            'contact_group_field' => $contactGroupField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_group_field_show', methods: ['GET'])]
    public function show(ContactGroupField $contactGroupField): Response
    {
        return $this->render('contact_group_field/show.html.twig', [
            'contact_group_field' => $contactGroupField,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_group_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactGroupField $contactGroupField, ContactGroupFieldRepository $contactGroupFieldRepository): Response
    {
        $form = $this->createForm(ContactGroupFieldType::class, $contactGroupField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactGroupFieldRepository->add($contactGroupField, true);

            return $this->redirectToRoute('app_contact_group_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact_group_field/edit.html.twig', [
            'contact_group_field' => $contactGroupField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_group_field_delete', methods: ['POST'])]
    public function delete(Request $request, ContactGroupField $contactGroupField, ContactGroupFieldRepository $contactGroupFieldRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contactGroupField->getId(), $request->request->get('_token'))) {
            $contactGroupFieldRepository->remove($contactGroupField, true);
        }

        return $this->redirectToRoute('app_contact_group_field_index', [], Response::HTTP_SEE_OTHER);
    }
}
