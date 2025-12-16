<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\ConnectionType;
use App\Entity\Connection;
use App\Repository\ConnectionRepository;
use App\Entity\User;
use App\Repository\UserRepository;

final class ConnectionController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/friend/add', name: 'app_friend_add')]
    public function addFriend(Request $request, Security $security, UserRepository $userRepository): RedirectResponse
    {
        $user = $security->getUser();

        $targetId = $request->query->get('target_id');

        if (!$targetId || $user === null) {
            throw new BadRequestHttpException('Invalid parameters');
        }

        $targetUser = $userRepository->find($targetId);

        if (!$targetUser || $targetUser === $user) {
            throw new BadRequestHttpException('Invalid target user');
        }
  
        $connection = findExistingConnection($user, ConnectionType::FRIEND, $targetUser);

        if($connection) {
            throw new BadRequestHttpException('Such a connection already exists');
        }
        
        $connection = findExistingConnection($user, ConnectionType::SUBSCRIBER, $targetUser);

        if ($subscriber) {
            if ($subscriber->getInitiator() === $targetUser) {
                $subscriber->setType(ConnectionType::FRIEND);
            } else {
                throw new BadRequestHttpException('Such a connection already exists');
            }
        } else {
            $subscriber = new Connection();
            $subscriber->setUser($user);
            $subscriber->setTargetId($targetId);
            $subscriber->setType(ConnectionType::SUBSCRIBER);
            $this->em->persist($subscriber);
        }

        $this->em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/jobRequest/add', name: 'app_job_request')]
    public function jobRequest(Request $request, Security $security, ConnectionRepository $connectionRepository): RedirectResponse
    {
        $user = $security->getUser();
        $targetId = $request->query->get('target_id');
        
        if (!$targetId || $user === null) {
            throw new BadRequestHttpException('Invalid parameters');
        }

        $company = $this->companyRepository->find($targetId);
        if(!$company)
        {
            throw new BadRequestHttpException('Invalid parameters');
        }


        $connection = $connectionRepository->findPendingJobRequest($user, $company);

        if($connection) {
            if ($connection->getTypes() === ConnectionType::REQUEST_USER_TO_COMPANY) {
                throw new BadRequestHttpException('The application has already been sent');
            }
            else {
                $connection->setTypes(ConnectionType::WORKER);
            }
        }
        else {
            $connection = new Connection();
            $company->setUserInitiator($user);
            $connection->setTargetId($company->getId());
            $connection->setType(ConnectionType::REQUEST_USER_TO_COMPANY);

            $this->em->persist($connection);
        }
        $this->em->flush();

        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/employee/add', name: 'app_adding_employee')]
    public function addEmployeeRequest(Request $request, Security $security, ConnectionRepository $connectionRepository): RedirectResponse
    {
        $targetId = $request->request-get('companyId');
        $user = $request->request-get('userId');

        if (!$targetId || $user === null) {
            throw new BadRequestHttpException('Invalid parameters');
        }

        $company = $this->companyRepository->find($targetId);
        if(!$company)
        {
            throw new BadRequestHttpException('Invalid parameters');
        }
        
        $connection = $connectionRepository->findPendingJobRequest($user, $company);

        if($connection) {
            if ($connection->getTypes() === ConnectionType::REQUEST_COMPANY_TO_USER) {
                throw new BadRequestHttpException('The offer has already been sent');
            }
            else {
                $connection->setTypes(ConnectionType::WORKER);
            }
        }
        else {
            $connection = new Connection();
            $company->setUserInitiator($user);
            $connection->setTargetId($company->getId());
            $connection->setType(ConnectionType::REQUEST_COMPANY_TO_USER);

            $this->em->persist($connection);
        }
        $this->em->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
