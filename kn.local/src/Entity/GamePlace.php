<?php

namespace App\Entity;

use App\Repository\GamePlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GamePlaceRepository::class)
 */
class GamePlace
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable="yes")
     */
    private $completed_at;

    /**
     * @ORM\Column(type="integer", nullable="yes")
     */
    public $player_one_id;

    /**
     * @ORM\Column(type="integer", nullable="yes")
     */
    public $player_second_id;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="game_place")
     */
    private $games;

    /**
     * @ORM\Column(type="integer", nullable="yes")
     */
    private $winner;

    /**
     * @ORM\Column(type="integer", nullable="yes")
     */
    private $invite_id;

    /**
     * @ORM\Column(type="string", length=255, nullable="yes")
     */
    private $cells;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function setCreatedAtValue()
    {
        $this->created_at = new \DateTime();
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completed_at;
    }

    public function setCompletedAt(\DateTimeInterface $completed_at): self
    {
        $this->completed_at = $completed_at;

        return $this;
    }

    public function setCompletedAtValue()
    {
        $this->completed_at = new \DateTime();
    }

    public function getPlayerOneId(): ?int
    {
        return $this->player_one_id;
    }

    public function setPlayerOneId(?int $player_one_id): self
    {
        $this->player_one_id = $player_one_id;

        return $this;
    }

    public function getPlayerSecondId(): ?int
    {
        return $this->player_second_id;
    }

    public function setPlayerSecondId(?int $player_second_id): self
    {
        $this->player_second_id = $player_second_id;

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setGamePlace($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getGamePlace() === $this) {
                $game->setGamePlace(null);
            }
        }

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner($winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getInviteId(): ?int
    {
        return $this->invite_id;
    }

    public function setInviteId(?int $invite_id): self
    {
        $this->invite_id = $invite_id;

        return $this;
    }

    public function getCells(): ?string
    {
        return $this->cells;
    }

    public function setCells(string $cells): self
    {
        $this->cells = $cells;

        return $this;
    }


}
