<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GamePlace;
use App\Entity\User;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ResultController extends BaseController
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
     * @var Security
     */
    private Security $security;

    public function __construct(GameRepository $gameRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/result", name="result")
     * @return Response
     */
    public function index(): Response
    {
        $games = $this->entityManager->getRepository(GamePlace::class)->findAll(); //Беру информацию о всех проведенных играх из БД

        foreach ($games as $game) //Перебираю массив из Бд и записываю все данные в переменные
        {
            $val[$game->getId()] = unserialize( $game->getCells());
            $first_player[$game->getId()] = $this->entityManager->getRepository(User::class)->find($game->getPlayerOneId());
            $second_player[$game->getId()] = $this->entityManager->getRepository(User::class)->find($game->getPlayerSecondId());
            $winner[$game->getId()] = $this->entityManager->getRepository(User::class)->find($game->getWinner());

        }

        return $this->render('result/index.html.twig', [
            'games' => $games,
            'values' => $val,
            'first_player' => $first_player,
            'second_player' => $second_player,
            'winner' => $winner,
        ]);
    }
}