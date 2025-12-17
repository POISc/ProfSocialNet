<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Company;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CompanyController extends AbstractController
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    #[Route('/company/{id}', name: 'company_show', requirements: ['id' => '\d+'])]
    public function show(Company $company): Response
    {
        return $this->render('company/index.html.twig', [
            'company' => $company
        ]);
    }

    #[Route('/company/create', name: 'company_create', methods: ['POST'])]
    public function createCompany(Request $request, Security $security): Response
    {
        $user = $this->security->getUser();

        if (!$this->isCsrfTokenValid('create_company', $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $name = trim($request->request->get('nameCompany'));

        if ($name === '') {
            throw new BadRequestHttpException('Company name is required');
        }

        $company = new Company();
        $company->setNameCompany($name);
        $company->setOwner($user);

        $this->em->persist($company);
        $this->em->flush();

        return $this->redirectToRoute('company_show', [
            'id' => $company->getId(),
        ]);
    }

    #[Route('/company/{id}/edit', name: 'company_edit_page', methods: ['GET'])]
    public function editCompanyPage(Company $company): Response
    {
        return $this->render('company/change.html.twig', [
            'company' => $company
        ]);
    }

    #[Route('/company/{id}/edit', name: 'company_edit', methods: ['PUT'])]
    public function editCompany(Request $request, Company $company, Security $security): Response
    {
        $user = $security->getUser();
        if ($company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('edit_company' . $company->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $name = trim($request->request->get('nameCompany'));
        if ($name === '') {
            throw new BadRequestHttpException('Company name is required');
        }

        $company->setNameCompany($name);
        $this->em->flush();

        return $this->redirectToRoute('company_show', ['id' => $company->getId()]);
    }

    #[Route('/company/{id}/delete', name: 'company_delete', methods: ['DELETE'])]
    public function deleteCompany(Request $request, Company $company, Security $security, SessionInterface $session): Response
    {
        $user = $security->getUser();
        if ($company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_company' . $company->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $this->em->remove($company);
        $this->em->flush();

        return $this->redirecToRoute('app_user', ['id' => $user]);
    }
}
