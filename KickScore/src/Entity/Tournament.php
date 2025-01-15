<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TOURNAMENT_TRM')]
#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TRM_ID')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $TRM_NAME = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TRM_START = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TRM_END = null;

    #[ORM\OneToOne(targetEntity: Championship::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'CHP_ID', referencedColumnName: 'CHP_ID', nullable: false)]
    private ?Championship $championship = null;

    //private ?BinaryVersus $final = null;
    private Versus $smallFinal;

    #[ORM\OneToOne(inversedBy: 'tournament', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'PLA_ID', referencedColumnName: 'PLA_ID', nullable: true)]
    private ?PlayoffMatch $final = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTRMNAME(): ?string
    {
        return $this->TRM_NAME;
    }

    public function setTRMNAME(string $TRM_NAME): static
    {
        $this->TRM_NAME = $TRM_NAME;

        return $this;
    }

    public function getTRMSTART(): ?\DateTimeInterface
    {
        return $this->TRM_START;
    }

    public function setTRMSTART(\DateTimeInterface $TRM_START): static
    {
        $this->TRM_START = $TRM_START;

        return $this;
    }

    public function getTRMEND(): ?\DateTimeInterface
    {
        return $this->TRM_END;
    }

    public function setTRMEND(\DateTimeInterface $TRM_END): static
    {
        $this->TRM_END = $TRM_END;

        return $this;
    }

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(Championship $championship): static
    {
        $this->championship = $championship;
    
        return $this;
    }
/*
    public function setInitMatches(int $depth, PlayoffMatch $match, array $matches)
    {

        if ($depth > 1)
        {
            return $this->setInitMatches($depth-1, $match->getPrevious()[0], $matches) + 
            $this->setInitMatches($depth-1, $match->getPrevious()[1], $matches);
        }
        $match = array_pop($matches);
        
        $match = new Versus;
        $randomKey = array_rand($this->teams);
        $match->setBlueTeam($this->teams[$randomKey]);
        unset($this->versus[$this->teams[$randomKey]]);

        $randomKey = array_rand($this->teams);
        $match->setGreenTeam($this->teams[$randomKey]);
        unset($this->versus[$this->teams[$randomKey]]);
        
        return $matches[] = $match;
    }
*/

/*
    public function __construct(int $depth, array $teams, array $versus)
    {
        $this->final = new BinaryVersus();
        $this->teams = $teams;
        $this->$versus;        
        $this->refreshTournament($final);
    }
*/

public function getFinal(): ?PlayoffMatch
{
    return $this->final;
}

public function setFinal(?PlayoffMatch $final): static
{
    $this->final = $final;

    return $this;
}

}
