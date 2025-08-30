<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;
use Nexendrie\Utils\Collection;

/**
 * Handles combat
 *
 * @author Jakub Konečný
 * @property-read int $winner Team which won the combat/0 if there is no winner yet
 * @property-read int $round Number of current round
 * @property int $roundLimit
 * @property Team $team1
 * @property Team $team2
 * @property-read int $team1Damage
 * @property-read int $team2Damage
 * @property Collection|ICombatAction[] $combatActions
 * @property callable $victoryCondition To evaluate the winner of combat. Gets combat as parameter, should return winning team (1/2) or 0 if there is not winner (yet)
 * @property callable $healers To determine characters that are supposed to heal their team. Gets team1 and team2 as parameters, should return Team
 * @method void onCombatStart(CombatBase $combat)
 * @method void onCombatEnd(CombatBase $combat)
 * @method void onRoundStart(CombatBase $combat)
 * @method void onRound(CombatBase $combat)
 * @method void onRoundEnd(CombatBase $combat)
 */
class CombatBase
{
    use \Nette\SmartObject;

    protected Team $team1;
    protected Team $team2;
    protected int $round = 0;
    protected int $roundLimit = 30;
    /** @var int[] Dealt damage by team */
    protected array $damage = [1 => 0, 2 => 0];
    /** @var callable[] */
    public array $onCombatStart = [];
    /** @var callable[] */
    public array $onCombatEnd = [];
    /** @var callable[] */
    public array $onRoundStart = [];
    /** @var callable[] */
    public array $onRound = [];
    /** @var callable[] */
    public array $onRoundEnd = [];
    /** @var callable */
    protected $victoryCondition;
    /** @var callable */
    protected $healers;
    /** @var Collection|ICombatAction[] */
    protected Collection $combatActions;

    public function __construct(
        public readonly CombatLogger $log,
        public ISuccessCalculator $successCalculator = new RandomSuccessCalculator(),
        public ICombatActionSelector $actionSelector = new CombatActionSelector()
    ) {
        $this->victoryCondition = [VictoryConditions::class, "moreDamage"];
        $this->healers = function (): Team {
            return new Team("healers");
        };
        $this->combatActions = new class extends Collection {
            protected string $class = ICombatAction::class;
        };
        $this->registerDefaultHandlers();
        $this->registerDefaultCombatActions();
    }

    protected function registerDefaultHandlers(): void
    {
        $this->onCombatStart[] = [$this, "assignPositions"];
        $this->onCombatEnd[] = [$this, "removeCombatEffects"];
        $this->onCombatEnd[] = [$this, "logCombatResult"];
        $this->onCombatEnd[] = [$this, "resetInitiative"];
        $this->onRoundStart[] = [$this, "applyEffectProviders"];
        $this->onRoundStart[] = [$this, "decreaseEffectsDuration"];
        $this->onRoundStart[] = [$this, "recalculateStats"];
        $this->onRoundStart[] = [$this, "logRoundNumber"];
        $this->onRoundStart[] = [$this, "applyPoison"];
        $this->onRound[] = [$this, "mainStage"];
        $this->onRoundEnd[] = [$this, "decreaseSkillsCooldowns"];
        $this->onRoundEnd[] = [$this, "resetInitiative"];
    }

    protected function registerDefaultCombatActions(): void
    {
        $this->combatActions[] = new CombatActions\Attack();
        $this->combatActions[] = new CombatActions\Heal();
        $this->combatActions[] = new CombatActions\SkillAttack();
        $this->combatActions[] = new CombatActions\SkillSpecial();
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function getRoundLimit(): int
    {
        return $this->roundLimit;
    }

    public function setRoundLimit(int $roundLimit): void
    {
        $this->roundLimit = Numbers::range($roundLimit, 1, PHP_INT_MAX);
    }

    /**
     * Set teams
     */
    public function setTeams(Team $team1, Team $team2): void
    {
        if (isset($this->team1)) {
            throw new ImmutableException("Teams has already been set.");
        }
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->log->setTeams($team1, $team2);
    }

    /**
     * Set participants for duel
     * Creates teams named after the member
     */
    public function setDuelParticipants(Character $player, Character $opponent): void
    {
        $team1 = new Team($player->name);
        $team1[] = $player;
        $team2 = new Team($opponent->name);
        $team2[] = $opponent;
        $this->setTeams($team1, $team2);
    }

    public function getTeam1(): Team
    {
        return $this->team1;
    }

    public function getTeam2(): Team
    {
        return $this->team2;
    }

    public function getVictoryCondition(): callable
    {
        return $this->victoryCondition;
    }

    public function setVictoryCondition(callable $victoryCondition): void
    {
        $this->victoryCondition = $victoryCondition;
    }

    public function getHealers(): callable
    {
        return $this->healers;
    }

    public function setHealers(callable $healers): void
    {
        $this->healers = $healers;
    }

    public function getTeam1Damage(): int
    {
        return $this->damage[1];
    }

    public function getTeam2Damage(): int
    {
        return $this->damage[2];
    }

    /**
     * @return Collection|ICombatAction[]
     */
    public function getCombatActions(): Collection
    {
        return $this->combatActions;
    }

    /**
     * Get winner of combat
     *
     * @staticvar int $result
     * @return int Winning team/0
     */
    public function getWinner(): int
    {
        static $result = 0;
        if ($result === 0) {
            $result = call_user_func($this->victoryCondition, $this);
            $result = Numbers::range($result, 0, 2);
        }
        return $result;
    }

    /**
     * @internal
     */
    public function getTeam(Character $character): Team
    {
        return $this->team1->hasItems(["id" => $character->id]) ? $this->team1 : $this->team2;
    }

    /**
     * @internal
     */
    public function getEnemyTeam(Character $character): Team
    {
        return $this->team1->hasItems(["id" => $character->id]) ? $this->team2 : $this->team1;
    }

    public function applyEffectProviders(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            $character->applyEffectProviders();
        }
    }

    public function assignPositions(self $combat): void
    {
        $assignPositions = function (Team $team): void {
            $row = 1;
            $column = 0;
            /** @var Character $character */
            foreach ($team as $character) {
                try {
                    $column++;
                    if ($character->positionRow > 0 && $character->positionColumn > 0) {
                        continue;
                    }
                    setPosition:
                    $team->setCharacterPosition($character->id, $row, $column);
                } catch (InvalidCharacterPositionException $e) {
                    if ($e->getCode() === InvalidCharacterPositionException::ROW_FULL) {
                        $row++;
                        $column = 1;
                    } elseif ($e->getCode() === InvalidCharacterPositionException::POSITION_OCCUPIED) {
                        $column++;
                    } else {
                        throw $e;
                    }
                    goto setPosition;
                }
            }
        };
        $assignPositions($combat->team1);
        $assignPositions($combat->team2);
    }

    public function decreaseEffectsDuration(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            foreach ($character->effects as $effect) {
                if (is_int($effect->duration)) {
                    $effect->duration--;
                }
            }
        }
    }

    /**
     * Decrease skills' cooldowns
     */
    public function decreaseSkillsCooldowns(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            foreach ($character->skills as $skill) {
                $skill->decreaseCooldown();
            }
        }
    }

    /**
     * Remove combat effects from character at the end of the combat
     */
    public function removeCombatEffects(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            $character->effects->removeByFilter(["duration!=" => CharacterEffect::DURATION_FOREVER]);
        }
    }

    /**
     * Add winner to the log
     */
    public function logCombatResult(self $combat): void
    {
        $combat->log->round = 5000;
        $params = [
            "team1name" => $combat->team1->name, "team1damage" => $combat->damage[1],
            "team2name" => $combat->team2->name, "team2damage" => $combat->damage[2],
        ];
        $params["winner"] = ($combat->winner === 1) ? $combat->team1->name : $combat->team2->name;
        $combat->log->logText("combat.log.combatEnd", $params);
    }

    /**
     * Log start of a round
     */
    public function logRoundNumber(self $combat): void
    {
        $combat->log->round = ++$this->round;
    }

    /**
     * Decrease duration of effects and recalculate stats
     */
    public function recalculateStats(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            $character->recalculateStats();
        }
    }

    /**
     * Reset characters' initiative
     */
    public function resetInitiative(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
        foreach ($characters as $character) {
            $character->resetInitiative();
        }
    }

    /**
     * Select target for attack
     *
     * @internal
     */
    public function selectAttackTarget(Character $attacker): ?Character
    {
        $enemyTeam = $this->getEnemyTeam($attacker);
        $rangedWeapon = ($attacker->equipment->hasItems(["%class%" => Weapon::class, "worn" => true, "ranged" => true,]));
        if (!$rangedWeapon) {
            $rowToAttack = $enemyTeam->rowToAttack;
            if ($rowToAttack === null) {
                return null;
            }
            $enemies = Team::fromArray(
                $enemyTeam->getItems(["positionRow" => $rowToAttack, "hitpoints>" => 0, "hidden" => false,]),
                $enemyTeam->name
            );
        } else {
            $enemies = $enemyTeam;
        }
        $target = $enemies->getLowestHpCharacter();
        if ($target !== null) {
            return $target;
        }
        return $enemies->getRandomCharacter();
    }

    /**
     * Main stage of a round
     *
     * @throws NotImplementedException
     */
    public function mainStage(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge($combat->team1->usableMembers, $combat->team2->usableMembers);
        usort($characters, function (Character $a, Character $b): int {
            return -1 * strcmp((string) $a->initiative, (string) $b->initiative);
        });
        foreach ($characters as $character) {
            /** @var ICombatAction|null $combatAction */
            $combatAction = $combat->actionSelector->chooseAction($combat, $character);
            if ($combatAction === null) {
                break;
            }
            $combatAction->do($combat, $character);
        }
    }

    /**
     * Executes the combat
     *
     * @return int Winning team
     */
    public function execute(): int
    {
        if (!isset($this->team1)) {
            throw new InvalidStateException("Teams are not set.");
        }
        $this->onCombatStart($this);
        while ($this->round <= $this->roundLimit) {
            $this->onRoundStart($this);
            if ($this->getWinner() > 0) {
                break;
            }
            $this->onRound($this);
            $this->onRoundEnd($this);
            if ($this->getWinner() > 0) {
                break;
            }
        }
        $this->onCombatEnd($this);
        return $this->getWinner();
    }

    /**
     * Harm poisoned characters at start of round
     */
    public function applyPoison(self $combat): void
    {
        /** @var Character[] $characters */
        $characters = array_merge(
            $combat->team1->getItems(["hitpoints>" => 0, "poisoned!=" => false,]),
            $combat->team2->getItems(["hitpoints>" => 0, "poisoned!=" => false,])
        );
        foreach ($characters as $character) {
            $poisonValue = $character->getStatus(Character::STATUS_POISONED);
            $character->harm($poisonValue);
            $action = [
                "action" => CombatLogEntry::ACTION_POISON, "result" => true, "amount" => $poisonValue,
                "character1" => $character, "character2" => $character,
            ];
            $combat->log->log($action);
        }
    }

    /**
     * Log dealt damage
     */
    public function logDamage(Character $attacker, int $amount): void
    {
        $team = $this->team1->hasItems(["id" => $attacker->id]) ? 1 : 2;
        $this->damage[$team] += $amount;
    }
}
