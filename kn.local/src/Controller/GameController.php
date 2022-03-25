<?php



namespace App\Controller;

use App\Entity\Game;
use App\Entity\GamePlace;
use App\Entity\Invite;
use App\Entity\User;
use App\Repository\GamePlaceInterface;
use App\Repository\GamePlaceRepository;
use App\Repository\GameRepository;
use App\Repository\InviteRepository;
use App\Service\GamePlaceService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class GameController extends AbstractController
{
    /**
     * @var GameRepository
     */
    private GameRepository $gameRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var GamePlaceRepository
     */
    private GamePlaceRepository $gamePlaceRepository;

    private Security $security;
    /**
     * @var InviteRepository
     */
    private InviteRepository $inviteRepository;


    public function __construct(GameRepository $gameRepository, EntityManagerInterface $entityManager, GamePlaceRepository $gamePlaceRepository, Security $security, InviteRepository $inviteRepository)
    {
        $this->gameRepository = $gameRepository;

        $this->entityManager = $entityManager;

        $this->gamePlaceRepository = $gamePlaceRepository;

        $this->security = $security;
        $this->inviteRepository = $inviteRepository;
    }


    /**
     * @Route("/testgame", name="game_test")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function testGameAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cell = $request->query->get('cell');   // Берем номер ячейки из гет-запроса

        $game_place = null;

        $this->gameRepository->createTestPlayerStep($cell);    //Ход игрока
        $this->gameRepository->createSystemStep($cell);    //Ход системы
        $showValue = $this->gamePlaceRepository->showValue();       //Показ ходов,ф-ция нужна чтобы выводить в шаблон ходы игроков

        $checkWin = $this->gameRepository->checkWin($game_place, $cell); //Определение победителя
        if ($checkWin == 'Выиграли Крестики') {
            $this->addFlash('success', 'Выиграли Крестики');    //Вывожу сообщение о победителе

        } elseif ($checkWin == 'Выиграли Нолики') {
            $this->addFlash('success', 'Выиграли Нолики');

        } elseif ($checkWin == 'Ничья') {
            $this->addFlash('success', 'Ничья');
        }


        return $this->render('game/testing.html.twig', [
            'controller_name' => 'GameController',
            'values' => $showValue,
        ]);
    }


    /**
     * @Route("/game/{game_place_id}", name="game")
     * @param Request $request
     * @param int $game_place_id
     * @return Response
     */
    public function gameAction(Request $request, int $game_place_id): Response
    {
        $id = $this->security->getUser()->getId(); //Получаем id текущего авторизованного пользователя
        $game_place = $this->entityManager->getRepository(GamePlace::class)->find($game_place_id); //Получаем данные текущей игры

        if ($game_place) { //Если в Бд есть данные текущей игры

            $player_first = $this->entityManager->getRepository(User::class)->find($game_place->getPlayerOneId()); //Находим в таблице пользователей данные об игроках

            $player_second = $this->entityManager->getRepository(User::class)->find($game_place->getPlayerSecondId());


            $showValue = $this->gamePlaceRepository->showValue(); //Показ ходов,ф-ция нужна чтобы выводить в шаблон ходы игроков
            $checkWin = $checkWin = $this->gameRepository->checkWin($game_place, null);

            return $this->render('game/index.html.twig', [
                'controller_name' => 'GameController',
                'values' => $showValue,
                'game_place_id' => $game_place_id, //Отправляю Id gamePlace в шаблон
                'checkWin' => $checkWin,
                'playerFirst' => $player_first,
                'playerSecond' => $player_second,
                'id' => $id,

            ]);
        } else {
            die('Данная игра еще не создана!');
        }


    }


    /**
     * @Route("/game-step/{game_place_id}", name="game_step")
     * @param Request $request
     * @param  $game_place_id
     * @return Response
     */
    public function stepAction(Request $request, $game_place_id): Response
    {

        $cell = $request->query->get('cell');   // Берем номер ячейки из гет-запроса
        $id = $this->security->getUser()->getId();

        $game_place = $this->entityManager->getRepository(GamePlace::class)->find($game_place_id);  //Получаем текущую игру

        $lastStep = $this->entityManager->getRepository(Game::class)->lastStep();  //Получаем последний сделанный ход игроков

        $player_first = $this->entityManager->getRepository(User::class)->find($game_place->getPlayerOneId()); //Находим в таблице пользователей данные об игроках, для вывода имени игрока с кем играем

        $player_second = $this->entityManager->getRepository(User::class)->find($game_place->getPlayerSecondId());


        $checkWin = $this->gameRepository->checkWin($game_place, $cell); //Определение победителя

        if ($checkWin != null) { //Если есть победитель
            $showValue = $this->gamePlaceRepository->showValue(); //Выводим ходы в игровом поле
            $value = serialize($showValue); //Сериализация ячеек для сохранения их в БД
            $game_place->setCompletedAtValue(); //Записываю время окончания игры
            $game_place->setCells($value); //Запись всех ходов игроков в БД

            $this->entityManager->flush();

            $winner = $this->entityManager->getRepository(User::class)->findBy(['id'=> $lastStep[0]['playerId'] ]);

            if ($checkWin == 'Выиграли Крестики') {

                $this->addFlash('success', "Выиграл ".$winner[0]->getName());    //Вывожу сообщение о победителе

            } elseif ($checkWin == 'Выиграли Нолики') {
                $this->addFlash('success', "Выиграл ".$winner[0]->getName());

            } elseif ($checkWin == 'Ничья') {
                $this->addFlash('success', 'Ничья');
            }

        } else {   //Если победителя нет, продолжаем ходы
            $this->gameRepository->createPlayerStep($cell, $game_place);    //Ход игрока
            $showValue = $this->gamePlaceRepository->showValue();       //Показ ходов,ф-ция нужна чтобы выводить в шаблон ходы игроков

            if ($lastStep){ //Смотрим чей сейчас ход и выводим сообщение
                if ($lastStep[0]['playerId'] == $id){
                    $this->addFlash('success', 'Ожидаем ход другого игрока');
                } else{
                    $this->addFlash('success', 'Сейчас Ваш ход');
                }
            }
        }

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            'values' => $showValue,
            'game_place_id' => $game_place->getId(), //Отправляю Id gamePlace в шаблон
            'checkWin' => $checkWin,
            'playerFirst' => $player_first,
            'playerSecond' => $player_second,
            'id' => $id,
            'step' => $lastStep,
        ]);

    }

    /**
     * @Route("/invite/{act}/{id}", name="invite")
     * @param Request $request
     * @param string $act
     * @param int $id
     * @return Response
     */
    public function inviteAction(Request $request, string $act, int $id): Response
    {
        $invite = $this->entityManager->getRepository(Invite::class)->find($id);    //Ищу в БД приглашение по Ид
        if ($invite) {
            if ($act === 'reject') {  //Если игрок отказывается от приглашения, удаляем его из БД
                $this->entityManager->remove($invite);
                $this->entityManager->flush();
            } else {
                $invite->setResult(true);  //Если принимает приглашение, записываем в БД что приглашение принято
                $game = $this->entityManager->getRepository(Game::class)->findAll();    //Удаляем данные предыдущей игры из Бд
                if ($game) {
                    foreach ($game as $value) {
                        $this->entityManager->remove($value);
                    }
                    $this->entityManager->flush();
                }
                $game_place = $this->entityManager->getRepository(GamePlace::class)->gamePlaceAction($invite->getSender(), $invite->getRecipient(), $invite->getId());  //Передаем данные для создания записи об Игре
                return $this->redirectToRoute('game', ['game_place_id' => $game_place->getId()]);  //Переходим на страницу с игровым полем
            }
        }
        return $this->redirectToRoute('main');
    }


    /**
     * @Route("/test", name="test")
     * @return Response
     */
    public function testGame(): Response  //Удаляем данные предыдущей игры из Бд, Для записи данных тестовой игры
    {
        $game = $this->entityManager->getRepository(Game::class)->findAll();
        if ($game) {
            foreach ($game as $value) {
                $this->entityManager->remove($value);
            }
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('game_test');
    }
}
