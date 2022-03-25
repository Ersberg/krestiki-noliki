<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=255, nullable="yes")
     */
    private $playerId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $cell;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $value;

    /**
     * @ORM\ManyToOne(targetEntity=GamePlace::class, inversedBy="games")
     * @ORM\JoinColumn(nullable= "yes")
     */
    private $gamePlace;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerId(): ?string
    {
        return $this->playerId;
    }

    public function setPlayerId($playerId): self
    {
        $this->playerId = $playerId;

        return $this;
    }

    public function getCell(): ?string
    {
        return $this->cell;
    }

    public function setCell(string $cell): self
    {
        $this->cell = $cell;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getGamePlace(): ?GamePlace
    {
        return $this->gamePlace;
    }

    public function setGamePlace($gamePlace): ?self
    {
        $this->gamePlace = $gamePlace;

        return $this;
    }


}
