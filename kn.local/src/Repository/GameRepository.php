<?php

namespace App\Repository;

use App\Controller\GameController;
use App\Entity\Field;
use App\Entity\Game;
use App\Entity\GamePlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var Security
     */
    private Security $security;
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $registry;
    /**
     * @var EntityManager
     */
    private EntityManager $manager;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;


    private array $cells = [              //Массив ячеек игрового поля
        'A1','B1','C1',
        'A2','B2','C2',
        'A3','B3','C3',
    ];

    /**
     * @var GamePlaceRepository
     */
    private GamePlaceRepository $gamePlaceRepository;
    /**
     * @var GamePlace
     */
    private GamePlace $gamePlace;


    /**
     * GameRepository constructor.
     * @param ManagerRegistry $registry
     * @param UserRepository $userRepository
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ManagerRegistry $registry, UserRepository $userRepository, Security $security, EntityManagerInterface $entityManager)
    {

        parent::__construct($registry, Game::class);
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->registry = $registry;
        $this->entityManager = $entityManager;

    }

// Запрос какой игрок совершил предыдущий ход в Бд

    public function lastStep()
    {
            $db = $this->createQueryBuilder('g')
                ->select( 'g.id','g.value', 'g.playerId')
                ->orderBy('g.id', 'DESC')
                ->setMaxResults('1')
            ;
            return $db->getQuery()->execute();

    }



// Ф-ция проверки выигрыша

    public function checkWin($game_place,$cell) // Проверка на выигрыш
    {
        if ($cell) { // Если в гет запросе есть ячейка

            $objects = $this->entityManager->getRepository(Game::class)->findAll();
            $val_x = []; //Массив для крестиков
            $val_o = []; //Массив для ноликов

            foreach ($objects as $object)   //Перебор всех обьектов из БД, и вывод пары Ячейка - Id игрока
            {

                if ($object->getValue() == 'X') // Проверка Id игрока
                {
                    $val_x[] = $object->getCell(); // Получаем ячейки с крестиками
                    $player_x = $object->getPlayerId();
                    sort($val_x); //Сортируем их по порядку
                } else // Проверка Id игрока (Id системы в БД записываем как Null)
                {
                    $val_o[] = $object->getCell(); // Получаем ячейки с ноликами
                    $player_o = $object->getPlayerId();
                    sort($val_o);
                }


            }

            if (array_intersect(['A1', 'B1', 'C1'],$val_x ) == ['A1', 'B1', 'C1']  or array_intersect(['A1', 'B2', 'C3'],$val_x ) == ['A1', 'B2', 'C3']  or array_intersect(['A1', 'A2', 'A3'],$val_x ) == ['A1', 'A2', 'A3']  or array_intersect(['B1', 'B2', 'B3'],$val_x ) == ['B1', 'B2', 'B3'] or array_intersect(['A3', 'B2', 'C1'],$val_x ) == ['A3', 'B2', 'C1'] or array_intersect(['C1', 'C2', 'C3'],$val_x ) == ['C1', 'C2', 'C3'] or array_intersect(['A2', 'B2', 'C2'],$val_x ) == ['A2', 'B2', 'C2'] or array_intersect(['A3', 'B3', 'C3'],$val_x ) == ['A3', 'B3', 'C3'])
            {
                if ($game_place != null) {
                    $game_place->setWinner($player_x);
                }
                $this->entityManager->flush();
                return 'Выиграли Крестики';
            } elseif (array_intersect(['A1', 'B1', 'C1'],$val_o ) == ['A1', 'B1', 'C1']  or array_intersect(['A1', 'B2', 'C3'],$val_o ) == ['A1', 'B2', 'C3']  or array_intersect(['A1', 'A2', 'A3'],$val_o ) == ['A1', 'A2', 'A3']  or array_intersect(['B1', 'B2', 'B3'],$val_o ) == ['B1', 'B2', 'B3'] or array_intersect(['A3', 'B2', 'C1'],$val_o ) == ['A3', 'B2', 'C1'] or array_intersect(['C1', 'C2', 'C3'],$val_o ) == ['C1', 'C2', 'C3'] or array_intersect(['A2', 'B2', 'C2'],$val_o ) == ['A2', 'B2', 'C2'] or array_intersect(['A3', 'B3', 'C3'],$val_o ) == ['A3', 'B3', 'C3'])
            {
                if ($game_place != null) {
                    $game_place->setWinner($player_o);
                }
                $this->entityManager->flush();
                return 'Выиграли Нолики'; //Если заполнено 9 ячеек и победителя нет, то ничья
            }elseif (count($objects) == '9')
            {
                if ($game_place != null) {
                    $game_place->setWinner(0);
                }
                $this->entityManager->flush();
                return 'Ничья';
            }

        }
    }


//Ходы пользователя при тестовой игре

    public function createTestPlayerStep($cell)
    {
        $game_place = null;

        $cells = $this->cells; //Массив ячеек
        if ($this->checkWin($game_place,$cell)) return false; //Проверяем есть ли победитель, если есть то ходить больше нельзя

        $value = $this->lastStep();   //Вызов ф-ции проверки предыдущего хода

        if ($cell) {                    //Валидация гет-запроса номера ячейки
            $key = array_search($cell, $cells); //Если ячейка входит в массив ячеек то валидно
            if ($key == true)
            {         //Если номер ячейки валидный, то

                $playerId = $this->security->getUser()->getId();    //Получаем текущий Id авторизованного пользователя

                $checkDoubleValue = $this->getEntityManager()->getRepository(Game::class)->findBy(['cell' => $cell]);  //Ф-ция для проверки дублированных ходов игрока

                if ($checkDoubleValue == null) {    //Если такого хода не было то

                    $em = $this->getEntityManager();
                    $game = new Game();             //Запись хода в Бд
                    $game->setPlayerId($playerId);
                    $game->setCell($cell);
                    if (!empty($value))  //Если предыдущий ход был
                    {

                        if ($this->checkStep() == false) //Проверяем кто совершил предыдущий ход
                        {
                            switch ($value['0']['value']) {
                                case 'O': //Если О то записываем Х и наоборот
                                case null:
                                    $game->setValue('X');
                                    break;
                                case 'X':
                                    $game->setValue('O');
                                    break;
                            }
                        }
                    } else   //Если это первый ход игрока то он всегда Х
                    {
                        $game->setValue('X');
                    }
                    $game->setGamePlace(null);

                    $em->persist($game);
                    $em->flush();

            }
          }

        }

    }

//Ходы компьютера при тестовой игре


    public function createSystemStep($cell)  //Создание хода системы
    {
        $checkCells = $this->checkCells();  //массив ячеек для хода системы
        $cells = $this->cells;

        $game_place = null;

        if ($this->checkWin($game_place,$cell)) return false;   //Проверяем есть ли победитель, если есть то ходить больше нельзя

        $checkStep = $this->checkStep(); // Вызов ф-ции Проверки хода

        if ($checkStep)  //Если предыдущий ход был
        {
            if(count($checkCells)> 1){  //Если в массиве ячеек для хода системы несколько вариантов
                $cell_rand = array_rand($checkCells, 1); //Выбираем случайную из них
                $rand_value = $checkCells[$cell_rand];
            }else{
                $rand_value = (implode("",$checkCells)); //Если только один вариант, преобразуем массив в строку и передаем в для записи в БД
            }
            $checkDoubleCell = $this->getEntityManager()->getRepository(Game::class)->findBy(['cell' => $rand_value]);   //Проверка на дубли в Бд
            if ($checkDoubleCell == null)   //Если дублей нет то
            {
                $em = $this->getEntityManager();    //Запись хода в Бд
                $game = new Game();
                $game->setPlayerId(null); //Ид системы равно Null
                $game->setCell($rand_value);
                $game->setValue('O');
                $game->setGamePlace($game_place); //Ид текущей игры
                $em->persist($game);
                $em->flush();
            }
        }

    }


    public function checkStep() //Проверка хода
    {

        $lastStep = $this->lastStep(); // Вывод предыдущего хода
        if(!empty($lastStep)) // Если массив не пустой
        {
            $playerId = $this->security->getUser()->getId();
            if ($lastStep['0']['playerId'] == $playerId) // Проверяем был ли предыдущий ход, ходом игрока
            {
                return true;
            }
        }
    }


    public function checkCells() //Алгоритм выбора хода компьютера
    {
        $cells = $this->cells; //Массив всех ячеек игрового поля
        $objects = $this->entityManager->getRepository(Game::class)->findAll(); //Берем все из таблицы Game

        if (count($objects) >= 2) {
            foreach ($objects as $object)   //Перебор всех полученных значений
            {
                if ($object->getValue() == 'X') // Получаем ячейки с крестиками (тк Игрок всегда играет крестиками, это ячейки Игрока)
                {
                    $val_x[] = $object->getCell();
                } elseif ($object->getValue() == 'O') {
                    $val_o[] = $object->getCell(); // Получаем ячейки с ноликами (это ячейки системы)

                }

            }

            //Здесь выбираем из массивов победных комбинаций, либо те в которых нет заполненных Крестиков, либо те где два значения Крестика, чтобы не дать Игроку сделать победный ход

                 if (count(array_intersect(['A1', 'B1', 'C1'], $val_x)) == 2 or count(array_intersect(['A1', 'B1', 'C1'], $val_x)) == null) {
                    return array_diff(['A1', 'B1', 'C1'], $val_o); //Проверяем и Возвращаем значения ячеек в которых еще нет Нолика
                } elseif (count(array_intersect(['A1', 'B2', 'C3'], $val_x)) == 2 or count(array_intersect(['A1', 'B2', 'C3'], $val_x)) == null) {
                    return array_diff(['A1', 'B2', 'C3'], $val_o);

                } elseif (count(array_intersect(['A1', 'A2', 'A3'], $val_x)) == 2 or count(array_intersect(['A1', 'A2', 'A3'], $val_x)) == null) {
                    return array_diff(['A1', 'A2', 'A3'], $val_o);

                } elseif (count(array_intersect(['B1', 'B2', 'B3'], $val_x)) == 2 or count(array_intersect(['B1', 'B2', 'B3'], $val_x)) == null ) {
                    return array_diff(['B1', 'B2', 'B3'], $val_o);

                } elseif (count(array_intersect(['A3', 'B2', 'C1'], $val_x)) == 2 or count(array_intersect(['A3', 'B2', 'C1'], $val_x)) == null ) {
                    return array_diff(['A3', 'B2', 'C1'], $val_o);

                } elseif (count(array_intersect(['C1', 'C2', 'C3'], $val_x)) == 2 or count(array_intersect(['C1', 'C2', 'C3'], $val_x)) == null) {
                    return array_diff(['C1', 'C2', 'C3'], $val_o);

                } elseif (count(array_intersect(['A2', 'B2', 'C2'], $val_x)) == 2 or count(array_intersect(['A2', 'B2', 'C2'], $val_x)) == null ) {
                     return array_diff(['A2', 'B2', 'C2'], $val_o);
                } elseif (count(array_intersect(['A3', 'B3', 'C3'], $val_x)) == 2 or count(array_intersect(['A3', 'B3', 'C3'], $val_x)) == null) {
                    return array_diff(['A3', 'B3', 'C3'], $val_o);
    }

        } elseif ($objects) {
            foreach ($objects as $object)   //Перебор всех полученных значений
                {
                    $val[] = $object->getCell(); // Получаем ячейки из Бд
                }
            return array_diff($cells, $val); //Проверяем свободные ячейки,сравнивая массив ячеек с уже заполненными ячейками
            }

    }


//Ходы пользователей при игре друг с другом

    public function createPlayerStep($cell, $game_place)
    {

        $cells = $this->cells; //Массив ячеек

        if ($this->checkWin($game_place,$cell))     //Проверяем есть ли победитель, если есть то ходить больше нельзя
        {
            return false;
        } else {

            $value = $this->lastStep();   //Вызов ф-ции проверки предыдущего хода

            if ($cell) {                    //Валидация гет-запроса номера ячейки
                $key = array_search($cell, $cells); //Если ячейка входит в массив ячеек то валидно
                if ($key == true) {         //Если номер ячейки валидный, то

                    $playerId = $this->security->getUser()->getId();    //Получаем текущий Id авторизованного пользователя

                    $checkDoubleValue = $this->getEntityManager()->getRepository(Game::class)->findBy(['cell' => $cell]);  //Ф-ция для проверки дублированных ходов игрока

                    if ($checkDoubleValue == null) {    //Если такого хода не было то

                        $em = $this->getEntityManager();
                        $game = new Game();             //Запись хода в Бд
                        $game->setPlayerId($playerId);
                        $game->setCell($cell);
                        if (!empty($value))  //Если предыдущий ход был
                        {

                            if ($value['0']['playerId'] !== $playerId) //Проверяем кто совершил предыдущий ход
                            {
                                switch ($value['0']['value']) {
                                    case 'O': //Если О то записываем Х и наоборот
                                    case null:
                                        $game->setValue('X');
                                        break;
                                    case 'X':
                                        $game->setValue('O');
                                        break;
                                }
                            } else {
                                return false;
                            }
                        } else   //Если это первый ход игрока то он всегда Х
                        {
                            $game->setValue('X');
                        }
                        $game->setGamePlace($game_place); //Кладу в базу Ид из gamePlace

                        $em->persist($game);
                        $em->flush();

                    }
                }

            }
        }

    }


}
















    // /**
    //  * @return Field[] Returns an array of Field objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Field
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

