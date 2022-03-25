<?php

namespace App\Repository;

use App\Controller\GameController;
use App\Entity\Game;
use App\Entity\GamePlace;
use App\Entity\Invite;
use App\Service\GamePlaceService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * @method GamePlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method GamePlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method GamePlace[]    findAll()
 * @method GamePlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GamePlaceRepository extends ServiceEntityRepository
{
    /**
     * @var GameRepository
     */
    private GameRepository $gameRepository;

    private Security $security;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var InviteRepository
     */
    private InviteRepository $inviteRepository;


    public function __construct(ManagerRegistry $registry, GameRepository $gameRepository, Security $security, EntityManagerInterface $entityManager,InviteRepository $inviteRepository)
    {
        parent::__construct($registry, GamePlace::class);
        $this->gameRepository = $gameRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;

        $this->inviteRepository = $inviteRepository;
    }


    public function gamePlaceTestAction() //Запись в БД игры между пользователем и компьютером
    {
        $playerId = $this->security->getUser()->getId(); //Беру Ид пользователя
        $game = $this->entityManager->getRepository(GamePlace::class)->findAll(); //Смотрю ходы игроков

       if(empty($game)) //Пока ходов еще нет,создаю gamePlace
        {
            $em = $this->entityManager;
            $gamePlace = new GamePlace();
            $gamePlace->setCreatedAtValue();
            $gamePlace->setPlayerOneId($playerId);
            $gamePlace->setPlayerSecondId(null); //Ид системы по умолчанию Null
            $gamePlace->setInviteId(null); //Приглашения при игре с компьютером тоже не отрпавляются
            $em->persist($gamePlace);
            $em->flush();
            return $gamePlace->getId(); //Возвращаю Ид GamePlace
        }

    }


    public function gamePlaceAction($playerOneId, $playerSecondId, $inviteId) //Запись в БД игры между пользователями
    {
        $game = $this->entityManager->getRepository(Game::class)->findAll(); //Смотрю ходы игроков

        if ($inviteId) {     //Если любой из игроков зашел в игру, то делаем запись о начале игры

            if (empty($game)) //Пока ходов еще нет,создаю gamePlace
            {
                $em = $this->entityManager;
                $gamePlace = new GamePlace();
                $gamePlace->setCreatedAtValue();
                $gamePlace->setPlayerSecondId($playerSecondId);
                $gamePlace->setPlayerOneId($playerOneId);
                $gamePlace->setInviteId($inviteId);
                $em->persist($gamePlace);
                $em->flush();
                return $gamePlace;
            }

        }
    }


    public function checkGamePlace() //Проверка записей об еще не сыгранных играх в Бд, для определенного пользователя
    {
        $playerId = $this->security->getUser()->getId(); //Беру Ид пользователя
        $db = $this->createQueryBuilder('g')
            ->where('g.player_one_id = :id')
            ->orWhere('g.player_second_id = :id')
            ->andWhere('g.winner is NULL')
            ->setParameter('id', $playerId)
        ;
        return $db->getQuery()->execute();


    }


    public function showValue() //Вывод ходов игроков
    {
        $objects = $this->entityManager->getRepository(Game::class)->findAll(); //Вывод из БД

        $values = [];
        foreach ($objects as $object)   //Перебор массива и вывод пары Ячейка - Значение
        {
            $values[$object->getCell()] = $object->getValue();
        }
        return $values;
    }

/*    public function showGamePlace()
    {
        $playerId = $this->security->getUser()->getId(); //Беру Ид пользователя
        $db = $this->createQueryBuilder('g')
            ->where('g.player_one_id = :id')
            ->orWhere('g.player_second_id = :id')
            ->setParameter('id', $playerId)
        ;
        return $db->getQuery()->execute();

    }*/

    public function showCell() //Вывод ходов игроков
    {
        $objects = $this->entityManager->getRepository(GamePlace::class)->findAll(); //Вывод из БД
        $db = $this->createQueryBuilder('g')
            ->select('g.cells')

        ;
        return $db->getQuery()->execute();
    }
}
