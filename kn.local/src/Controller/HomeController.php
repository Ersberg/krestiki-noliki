<?php

namespace App\Controller;

use App\Entity\GamePlace;
use App\Entity\Invite;
use App\Entity\User;
use App\Repository\GamePlaceRepository;
use App\Repository\InviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    /**
     * @var InviteRepository
     */
    private InviteRepository $inviteRepository;
    /**
     * @var Security
     */
    private Security $security;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var GamePlaceRepository
     */
    private GamePlaceRepository $gamePlaceRepository;

    public function __construct(InviteRepository $inviteRepository, Security $security, EntityManagerInterface $entityManager, GamePlaceRepository $gamePlaceRepository)
    {
        $this->inviteRepository = $inviteRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->gamePlaceRepository = $gamePlaceRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response  //Страница которую видит пользователь без авторизации
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('main');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/main", name="main")
     * @param Request $request
     * @return Response
     */
    public function mainPage(Request $request): Response //Главная страница сайта
    {

        $checkInvite = $this->inviteRepository->checkInvite();      //Проверяем есть ли приглашение для этого пользователя

        if ($checkInvite)   //Если есть, выводим сообщение о приглашении
        {
            foreach ($checkInvite as $invite){  //Берем данные игрока, который отправил приглашение и выводим в сообщении
                $player_first = $this->entityManager->getRepository(User::class)->find($invite->getSender());
                $first_name = $player_first->getName();
                $first_mail = $player_first->getEmail();
            }

            $this->addFlash('success', ("Вам пришло приглашение в игру от игрока - " .$first_name." (".$first_mail. ")! " ."Нажмите 'Принять', чтобы начать игру, либо откажитесь от приглашения"));
        }

        return $this->render('home/main.html.twig', [
            'controller_name' => 'HomeController',
            'invite' => $checkInvite,
        ]);
    }


    /**
     * @Route("/main/game", name="main_game")
     * @param Request $request
     * @return Response
     */
    public function mainGame(Request $request): Response
    {
        $game_place = $this->gamePlaceRepository->checkGamePlace(); //Проверяем есть ли созданная еще не сыгранная игра с участием этого Игрока

        if($game_place){ //Если есть перенаправляем его на страницу игры, с игровым полем
            $game_place_id = $this->entityManager->getRepository(GamePlace::class)->find($game_place[0]->getId()); //Ищем в БД игру по ИД
            return $this->redirectToRoute('game', ['game_place_id' => $game_place_id->getId()]);
            }elseif (empty($game_place)){
                return $this->redirectToRoute('main'); //Если нет,оставляем на этой странице
            }
    }
}

