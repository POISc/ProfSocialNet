<?php

namespace App\Controller;

use App\Entity\Vacancy;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\SecurityBundle\Security;

class VacancyController extends AbstractController
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    #[Route('/vacancy/create', name: 'vacancy_create', methods: ['POST'])]
    public function createVacancy(Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('create_vacancy', $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $name = trim($request->request->get('nameVacancy'));
        $description = trim($request->request->get('description'));
        $skills = trim($request->request->get('skills'));
        $companyId = $request->request->get('company');

        if ($name === '' || !$companyId) {
            throw new BadRequestHttpException('Vacancy name and company are required');
        }

        $company = $this->em->getRepository(Company::class)->find($companyId);
        if (!$company) {
            throw new BadRequestHttpException('Company not found');
        }

        $vacancy = new Vacancy();
        $vacancy->setNameVacancy($name);
        $vacancy->setDescription($description);
        $vacancy->setSkills($skills);
        $vacancy->setCompany($company);

        $this->em->persist($vacancy);
        $this->em->flush();

        return $this->redirectToRoute('company_show', [
            'id' => $companyId,
        ]);
    }

    #[Route('/vacancy/{id}/delete', name: 'vacancy_delete', methods: ['DELETE'])]
    public function deleteVacancy(Vacancy $vacancy, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'delete_vacancy' . $vacancy->getId(),
            $request->request->get('_token')
        )) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        if ($vacancy->getCompany()->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $companyId = $vacancy->getCompany()->getId();

        $this->em->remove($vacancy);
        $this->em->flush();

        return $this->redirectToRoute('company_show', [
            'id' => $companyId,
        ]);
    }
}
