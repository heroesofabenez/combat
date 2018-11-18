<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;
use HeroesofAbenez\Combat\CombatActions\ICombatAction;
use Nexendrie\Utils\Collection;

/**
 * Handles combat
 * 
 * @author Jakub Konečný
 * @property-read CombatLogger $log Log from the combat
 * @property-read int $winner Team which won the combat/0 if there is no winner yet
 * @property-read int $round Number of current round
 * @property-read int $roundLimit
 * @property-read Team $team1
 * @property-read Team $team2
 * @property-read int $team1Damage
 * @property-read int $team2Damage
 * @property ISuccessCalculator $successCalculator
 * @property ICombatActionSelector $actionSelector
 * @property callable $victoryCondition To evaluate the winner of combat. Gets combat as parameter, should return winning team (1/2) or 0 if there is not winner (yet)
 * @property callable $healers To determine characters that are supposed to heal their team. Gets team1 and team2 as parameters, should return Team
 * @method void onCombatStart(CombatBase $combat)
 * @method void onCombatEnd(CombatBase $combat)
 * @method void onRoundStart(CombatBase $combat)
 * @method void onRound(CombatBase $combat)
 * @method void onRoundEnd(CombatBase $combat)
 */
class CombatBase {
  use \Nette\SmartObject;
  
  /** @var Team First team */
  protected $team1;
  /** @var Team Second team */
  protected $team2;
  /** @var CombatLogger */
  protected $log;
  /** @var int Number of current round */
  protected $round = 0;
  /** @var int Round limit */
  protected $roundLimit = 30;
  /** @var array Dealt damage by team */
  protected $damage = [1 => 0, 2 => 0];
  /** @var callable[] */
  public $onCombatStart = [];
  /** @var callable[] */
  public $onCombatEnd = [];
  /** @var callable[] */
  public $onRoundStart = [];
  /** @var callable[] */
  public $onRound = [];
  /** @var callable[] */
  public $onRoundEnd = [];
  /** @var callable */
  protected $victoryCondition;
  /** @var callable */
  protected $healers;
  /** @var ISuccessCalculator */
  protected $successCalculator;
  /** @var ICombatActionSelector */
  protected $actionSelector;
  /** @var Collection|ICombatAction[] */
  public $combatActions;
  
  public function __construct(CombatLogger $logger, ?ISuccessCalculator $successCalculator = null, ?ICombatActionSelector $actionSelector = null) {
    $this->log = $logger;
    $this->onCombatStart[] = [$this, "applyEffectProviders"];
    $this->onCombatStart[] = [$this, "setSkillsCooldowns"];
    $this->onCombatStart[] = [$this, "assignPositions"];
    $this->onCombatEnd[] = [$this, "removeCombatEffects"];
    $this->onCombatEnd[] = [$this, "logCombatResult"];
    $this->onCombatEnd[] = [$this, "resetInitiative"];
    $this->onRoundStart[] = [$this, "decreaseEffectsDuration"];
    $this->onRoundStart[] = [$this ,"recalculateStats"];
    $this->onRoundStart[] = [$this, "logRoundNumber"];
    $this->onRoundStart[] = [$this, "applyPoison"];
    $this->onRound[] = [$this, "mainStage"];
    $this->onRoundEnd[] = [$this, "decreaseSkillsCooldowns"];
    $this->onRoundEnd[] = [$this, "resetInitiative"];
    $this->victoryCondition = [VictoryConditions::class, "moreDamage"];
    $this->successCalculator = $successCalculator ?? new RandomSuccessCalculator();
    $this->actionSelector = $actionSelector ?? new CombatActionSelector();
    $this->healers = function(): Team {
      return new Team("healers");
    };
    $this->combatActions = new class extends Collection {
      /** @var string */
      protected $class = ICombatAction::class;
    };
    $this->combatActions[] = new CombatActions\Attack();
    $this->combatActions[] = new CombatActions\Heal();
    $this->combatActions[] = new CombatActions\SkillAttack();
    $this->combatActions[] = new CombatActions\SkillSpecial();
  }
  
  public function getRound(): int {
    return $this->round;
  }
  
  public function getRoundLimit(): int {
    return $this->roundLimit;
  }
  
  /**
   * Set teams
   */
  public function setTeams(Team $team1, Team $team2): void {
    if(isset($this->team1)) {
      throw new ImmutableException("Teams has already been set.");
    }
    $this->team1 = & $team1;
    $this->team2 = & $team2;
    $this->log->setTeams($team1, $team2);
  }
  
  /**
   * Set participants for duel
   * Creates teams named after the member
   */
  public function setDuelParticipants(Character $player, Character $opponent): void {
    $team1 = new Team($player->name);
    $team1[] = $player;
    $team2 = new Team($opponent->name);
    $team2[] = $opponent;
    $this->setTeams($team1, $team2);
  }
  
  public function getTeam1(): Team {
    return $this->team1;
  }
  
  public function getTeam2(): Team {
    return $this->team2;
  }
  
  public function getVictoryCondition(): callable {
    return $this->victoryCondition;
  }
  
  public function setVictoryCondition(callable $victoryCondition): void {
    $this->victoryCondition = $victoryCondition;
  }
  
  public function getHealers(): callable {
    return $this->healers;
  }
  
  public function setHealers(callable $healers): void {
    $this->healers = $healers;
  }
  
  public function getTeam1Damage(): int {
    return $this->damage[1];
  }
  
  public function getTeam2Damage(): int {
    return $this->damage[2];
  }
  
  public function getSuccessCalculator(): ISuccessCalculator {
    return $this->successCalculator;
  }
  
  public function setSuccessCalculator(ISuccessCalculator $successCalculator): void {
    $this->successCalculator = $successCalculator;
  }
  
  public function getActionSelector(): ICombatActionSelector {
    return $this->actionSelector;
  }
  
  public function setActionSelector(ICombatActionSelector $actionSelector): void {
    $this->actionSelector = $actionSelector;
  }
  
  /**
   * Get winner of combat
   * 
   * @staticvar int $result
   * @return int Winning team/0
   */
  public function getWinner(): int {
    static $result = 0;
    if($result === 0) {
      $result = call_user_func($this->victoryCondition, $this);
      $result = Numbers::range($result, 0, 2);
    }
    return $result;
  }

  /**
   * @internal
   */
  public function getTeam(Character $character): Team {
    return $this->team1->hasItems(["id" => $character->id]) ? $this->team1 : $this->team2;
  }

  /**
   * @internal
   */
  public function getEnemyTeam(Character $character): Team {
    return $this->team1->hasItems(["id" => $character->id]) ? $this->team2 : $this->team1;
  }
  
  public function applyEffectProviders(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      $character->applyEffectProviders();
    }
  }
  
  /**
   * Set skills' cooldowns
   */
  public function setSkillsCooldowns(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      foreach($character->skills as $skill) {
        $skill->resetCooldown();
      }
    }
  }
  
  public function assignPositions(self $combat): void {
    $assignPositions = function(Team $team) {
      $row = 1;
      $column = 0;
      /** @var Character $character */
      foreach($team as $character) {
        try {
          $column++;
          if($character->positionRow > 0 AND $character->positionColumn > 0) {
            continue;
          }
          setPosition:
          $team->setCharacterPosition($character->id, $row, $column);
        } catch(InvalidCharacterPositionException $e) {
          if($e->getCode() === InvalidCharacterPositionException::ROW_FULL) {
            $row++;
            $column = 1;
          } elseif($e->getCode() === InvalidCharacterPositionException::POSITION_OCCUPIED) {
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
  
  public function decreaseEffectsDuration(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      foreach($character->effects as $effect) {
        if(is_int($effect->duration)) {
          $effect->duration--;
        }
      }
    }
  }
  
  /**
   * Decrease skills' cooldowns
   */
  public function decreaseSkillsCooldowns(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      foreach($character->skills as $skill) {
        $skill->decreaseCooldown();
      }
    }
  }
  
  /**
   * Remove combat effects from character at the end of the combat
   */
  public function removeCombatEffects(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      foreach($character->effects as $effect) {
        if($effect->duration === CharacterEffect::DURATION_COMBAT OR is_int($effect->duration)) {
          $character->removeEffect($effect->id);
        }
      }
    }
  }
  
  /**
   * Add winner to the log
   */
  public function logCombatResult(self $combat): void {
    $combat->log->round = 5000;
    $params = [
      "team1name" => $combat->team1->name, "team1damage" => $combat->damage[1],
      "team2name" => $combat->team2->name, "team2damage" => $combat->damage[2],
    ];
    if($combat->winner === 1) {
      $params["winner"] = $combat->team1->name;
    } else {
      $params["winner"] = $combat->team2->name;
    }
    $combat->log->logText("combat.log.combatEnd", $params);
  }
  
  /**
   * Log start of a round
   */
  public function logRoundNumber(self $combat): void {
    $combat->log->round = ++$this->round;
  }
  
  /**
   * Decrease duration of effects and recalculate stats
   */
  public function recalculateStats(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      $character->recalculateStats();
    }
  }
  
  /**
   * Reset characters' initiative
   */
  public function resetInitiative(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->toArray(), $combat->team2->toArray());
    foreach($characters as $character) {
      $character->resetInitiative();
    }
  }
  
  /**
   * Select target for attack
   *
   * @internal
   */
  public function selectAttackTarget(Character $attacker): ?Character {
    $enemyTeam = $this->getEnemyTeam($attacker);
    $rangedWeapon = false;
    foreach($attacker->equipment as $equipment) {
      if($equipment instanceof Weapon AND $equipment->isWorn() AND $equipment->ranged) {
        $rangedWeapon = true;
        break;
      }
    }
    if(!$rangedWeapon) {
      $rowToAttack = $enemyTeam->rowToAttack;
      if(is_null($rowToAttack)) {
        return null;
      }
      /** @var Team $enemies */
      $enemies = Team::fromArray($enemyTeam->getItems(["positionRow" => $rowToAttack, "hitpoints>" => 0,]), $enemyTeam->name);
    } else {
      $enemies = $enemyTeam;
    }
    $target = $enemies->getLowestHpCharacter();
    if(!is_null($target)) {
      return $target;
    }
    return $enemies->getRandomCharacter();
  }
  
  /**
   * Select target for healing
   *
   * @internal
   */
  public function selectHealingTarget(Character $healer): ?Character {
    return $this->getTeam($healer)->getLowestHpCharacter();
  }
  
  /**
   * @internal
   */
  public function findHealers(): Team {
    $healers = call_user_func($this->healers, $this->team1, $this->team2);
    if($healers instanceof Team) {
      return $healers;
    }
    return new Team("healers");
  }
  
  /**
   * Main stage of a round
   *
   * @throws NotImplementedException
   */
  public function mainStage(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->usableMembers, $combat->team2->usableMembers);
    usort($characters, function(Character $a, Character $b) {
      return -1 * strcmp((string) $a->initiative, (string) $b->initiative);
    });
    foreach($characters as $character) {
      $action = $combat->actionSelector->chooseAction($combat, $character);
      if(is_null($action)) {
        break;
      }
      /** @var ICombatAction $combatAction */
      foreach($this->combatActions as $combatAction) {
        if($combatAction->getName() === $action) {
          $combatAction->do($this, $character);
          continue 2;
        }
      }
      throw new NotImplementedException("Action $action is not implemented.");
    }
  }
  
  /**
   * Start next round
   * 
   * @return int Winning team/0
   */
  protected function startRound(): int {
    $this->onRoundStart($this);
    return $this->getWinner();
  }
  
  /**
   * Do a round
   */
  protected function doRound(): void {
    $this->onRound($this);
  }
  
  /**
   * End round
   * 
   * @return int Winning team/0
   */
  protected function endRound(): int {
    $this->onRoundEnd($this);
    return $this->getWinner();
  }
  
  /**
   * Executes the combat
   * 
   * @return int Winning team
   */
  public function execute(): int {
    if(!isset($this->team1)) {
      throw new InvalidStateException("Teams are not set.");
    }
    $this->onCombatStart($this);
    while($this->round <= $this->roundLimit) {
      if($this->startRound() > 0) {
        break;
      }
      $this->doRound();
      if($this->endRound() > 0) {
        break;
      }
    }
    $this->onCombatEnd($this);
    return $this->getWinner();
  }

  /**
   * Harm poisoned characters at start of round
   */
  public function applyPoison(self $combat): void {
    /** @var Character[] $characters */
    $characters = array_merge($combat->team1->aliveMembers, $combat->team2->aliveMembers);
    foreach($characters as $character) {
      foreach($character->effects as $effect) {
        if($effect->type === SkillSpecial::TYPE_POISON) {
          $character->harm($effect->value);
          $action = [
            "action" => CombatLogEntry::ACTION_POISON, "result" => true, "amount" => $effect->value,
            "character1" => $character, "character2" => $character,
          ];
          $combat->log->log($action);
        }
      }
    }
  }
  
  /**
   * Log dealt damage
   */
  public function logDamage(Character $attacker, int $amount): void {
    $team = $this->team1->hasItems(["id" => $attacker->id]) ? 1 : 2;
    $this->damage[$team] += $amount;
  }
  
  public function getLog(): CombatLogger {
    return $this->log;
  }
}
?>