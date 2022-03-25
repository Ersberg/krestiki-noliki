<?php

namespace App\Repository;

use App\Entity\Invite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Invite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invite[]    findAll()
 * @method Invite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InviteRepository extends ServiceEntityRepository
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
     * @var ManagerRegistry
     */
    private ManagerRegistry $registry;


    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager, Security $security)
    {
        parent::__construct($registry, Invite::class);
        $this->manager = $manager;
        $this->security = $security;
        $this->registry = $registry;

    }

    public function createInvite($recipientId)  //Создание приглашения на игру
    {
        $senderId = $this->security->getUser()->getId(); // Берем Ид который делает приглашение игрока
        $manager = $this->getEntityManager();
        $res =  $this->getEntityManager()->getRepository(Invite::class)->findBy(['sender' => $senderId,'recipient' => $recipientId, 'result' => false]); //Смотрим в БД нет ли еще непринятых приглашений этому игроку

        if (!$res){ //Если непринятых приглашений между этими игроками нет, то создаем приглашение
            $invite = new Invite();
            $invite->setSender($senderId);
            $invite->setRecipient($recipientId);
            $invite->setCreatedAtValue();
            $invite->setResult(false);
            $manager->persist($invite);
            $manager->flush();
        }

    }

    public function checkInvite() //Проверка приглашений
    {
       $id = $this->security->getUser()->getId();
       $res =  $this->getEntityManager()->getRepository(Invite::class)->findBy(['recipient' => $id, 'result' => false]);

       if($res) {   //Проверяем есть ли приглашения
               return $res;
           } else {
               return false;
           }
    }



}
