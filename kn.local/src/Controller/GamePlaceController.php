<?php

namespace App\Controller;

use App\Entity\GamePlace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GamePlaceController extends AbstractController
{
    
    /**
     * @Route("/game", name="game_place")
     */
/*    public function index(): Response
    {
        $game = $this->getEntity()->getRepository(GamePlace::class)->findAll();
        return $this->render('game/index.html.twig', [
            'controller_name' => 'GamePlaceController',
        ]);
    }*/
}
