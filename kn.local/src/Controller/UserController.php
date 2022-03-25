<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        $render = parent::renderDefault();
        $render['title'] = 'Пользователи';
        $render['users'] = $this->userRepository->findUsers(); //Выводим список пользователей, с возможностью отправить им приглашение на игру
        return $this->render('user/index.html.twig', $render);

    }
}
