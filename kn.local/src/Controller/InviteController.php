<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Form\SendType;
use App\Repository\InviteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InviteController extends BaseController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;
    /**
     * @var Security
     */
    private Security $security;
    /**
     * @var InviteRepository
     */
    private InviteRepository $inviteRepository;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;





    public function __construct(EntityManagerInterface $manager, Security $security, InviteRepository $inviteRepository, UserRepository $userRepository)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->inviteRepository = $inviteRepository;
        $this->userRepository = $userRepository;

    }

    public function index(): Response
    {
        return $this->render('invite/index.html.twig', [
            'controller_name' => 'InviteController',
        ]);
    }

    /**
     * @Route("/user/invite", name="user_invite")
     * @param Request $request
     * @return Response
     */
    public function inviteAction (Request $request)
    {
        $senderId = $this->security->getUser()->getId(); // Берем Ид который делает приглашение игрока
        $recipientId = $request->query->get('id');     // Берем Ид игрока которого хотим пригласить из гет-запроса

        $checkResult = $this->manager->getRepository(Invite::class)->createQueryBuilder('i') //Проверяем результаты приглашений от имени игрока
            ->where('i.sender = :senderId')
            ->andWhere('i.recipient = :recipientId')
            ->setParameter('senderId', $senderId)
            ->setParameter('recipientId', $recipientId);
        $checkResult->getQuery()->getResult();

        $checkDoubleInvite = $this->manager->getRepository(Invite::class)->findBy(['sender' => $senderId,'recipient' => $recipientId, 'result' => false]); //Смотрим в БД нет ли еще непринятых приглашений этому игроку
        if ($checkDoubleInvite) {
            $this->addFlash('danger', 'Игроку уже отправлено приглашение!');
        }else {
            $this->addFlash('success', 'Приглашение отправлено игроку!');
        }
        $invite = $this->inviteRepository->createInvite($recipientId);//Создаем приглашение

        return $this->redirectToRoute('user');
    }
}
