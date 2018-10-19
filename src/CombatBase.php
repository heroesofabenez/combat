<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;

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
 * @method void onAttack(Character $attacker, Character $defender)
 * @method void onSkillAttack(Character $attacker, Character $defender, CharacterAttackSkill $skill)
 * @method void onSkillSpecial(Character $character1, Character $target, CharacterSpecialSkill $skill)
 * @method void onHeal(Character $healer, Character $patient)
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
  /** @var callable[] */
  public $onAttack = [];
  /** @var callable[] */
  public $onSkillAttack = [];
  /** @var callable[] */
  public $onSkillSpecial = [];
  /** @var callable[] */
  public $onHeal = [];
  /** @var callable */
  protected $victoryCondition;
  /** @var callable */
  protected $healers;
  /** @var ISuccessCalculator */
  protected $successCalculator;
  /** @var ICombatActionSelector */
  protected $actionSelector;
  
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
    $this->onAttack[] = [$this, "attackHarm"];
    $this->onSkillAttack[] = [$this, "useAttackSkill"];
    $this->onSkillSpecial[] = [$this, "useSpecialSkill"];
    $this->onHeal[] = [$this, "heal"];
    $this->victoryCondition = [VictoryConditions::class, "moreDamage"];
    $this->successCalculator = $successCalculator ?? new RandomSuccessCalculator();
    $this->actionSelector = $actionSelector ?? new CombatActionSelector();
    $this->healers = function(): Team {
      return new Team("healers");
    };
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
  
  protected function getTeam(Character $character): Team {
    return $this->team1->hasItems(["id" => $character->id]) ? $this->team1 : $this->team2;
  }
  
  protected function getEnemyTeam(Character $character): Team {
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
   * @throws NotImplementedException
   */
  protected function doAttackSkill(Character $character, CharacterAttackSkill $skill): void {
    $targets = [];
    /** @var Character $primaryTarget */
    $primaryTarget = $this->selectAttackTarget($character);
    switch($skill->skill->target) {
      case SkillAttack::TARGET_SINGLE:
        $targets[] = $primaryTarget;
        break;
      case SkillAttack::TARGET_ROW:
        $targets = $this->getTeam($primaryTarget)->getItems(["positionRow" => $primaryTarget->positionRow]);
        break;
      case SkillAttack::TARGET_COLUMN:
        $targets = $this->getTeam($primaryTarget)->getItems(["positionColumn" => $primaryTarget->positionColumn]);
        break;
      default:
        throw new NotImplementedException("Target {$skill->skill->target} for attack skills is not implemented.");
    }
    foreach($targets as $target) {
      for($i = 1; $i <= $skill->skill->strikes; $i++) {
        $this->onSkillAttack($character, $target, $skill);
      }
    }
  }
  
  /**
   * @throws NotImplementedException
   */
  protected function doSpecialSkill(Character $character, CharacterSpecialSkill $skill): void {
    $targets = [];
    switch($skill->skill->target) {
      case SkillSpecial::TARGET_ENEMY:
        $targets[] = $this->selectAttackTarget($character);
        break;
      case SkillSpecial::TARGET_SELF:
        $targets[] = $character;
        break;
      case SkillSpecial::TARGET_PARTY:
        $targets = $this->getTeam($character)->toArray();
        break;
      case SkillSpecial::TARGET_ENEMY_PARTY:
        $targets = $this->getEnemyTeam($character)->toArray();
        break;
      default:
        throw new NotImplementedException("Target {$skill->skill->target} for special skills is not implemented.");
    }
    foreach($targets as $target) {
      $this->onSkillSpecial($character, $target, $skill);
    }
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
      switch($action) {
        case CombatAction::ACTION_ATTACK:
          $combat->onAttack($character, $combat->selectAttackTarget($character));
          break;
        case CombatAction::ACTION_SKILL_ATTACK:
          /** @var CharacterAttackSkill $skill */
          $skill = $character->usableSkills[0];
          $combat->doAttackSkill($character, $skill);
          break;
        case CombatAction::ACTION_SKILL_SPECIAL:
          /** @var CharacterSpecialSkill $skill */
          $skill = $character->usableSkills[0];
          $combat->doSpecialSkill($character, $skill);
          break;
        case CombatAction::ACTION_HEALING:
          $combat->onHeal($character, $combat->selectHealingTarget($character));
          break;
        default:
          throw new NotImplementedException("Action $action is not implemented.");
      }
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
   * Do an attack
   * Hit chance = Attacker's hit - Defender's dodge, but at least 15%
   * Damage = Attacker's damage - defender's defense
   */
  public function attackHarm(Character $attacker, Character $defender): void {
    $result = [];
    $result["result"] = $this->successCalculator->hasHit($attacker, $defender);
    $result["amount"] = 0;
    if($result["result"]) {
      $amount = $attacker->damage - $defender->defense;
      $result["amount"] = Numbers::range($amount, 0, $defender->hitpoints);
    }
    if($result["amount"] > 0) {
      $defender->harm($result["amount"]);
    }
    $result["action"] = CombatAction::ACTION_ATTACK;
    $result["name"] = "";
    $result["character1"] = $attacker;
    $result["character2"] = $defender;
    $this->logDamage($attacker, $result["amount"]);
    $this->log->log($result);
  }
  
  /**
   * Use an attack skill
   */
  public function useAttackSkill(Character $attacker, Character $defender, CharacterAttackSkill $skill): void {
    $result = [];
    $result["result"] = $this->successCalculator->hasHit($attacker, $defender, $skill);
    $result["amount"] = 0;
    if($result["result"]) {
      $amount = (int) ($attacker->damage - $defender->defense / 100 * $skill->damage);
      $result["amount"] = Numbers::range($amount, 0, $defender->hitpoints);
    }
    if($result["amount"] > 0) {
      $defender->harm($result["amount"]);
    }
    $result["action"] = CombatAction::ACTION_SKILL_ATTACK;
    $result["name"] = $skill->skill->name;
    $result["character1"] = $attacker;
    $result["character2"] = $defender;
    $this->logDamage($attacker, $result["amount"]);
    $this->log->log($result);
    $skill->resetCooldown();
  }
  
  /**
   * Use a special skill
   */
  public function useSpecialSkill(Character $character1, Character $target, CharacterSpecialSkill $skill): void {
    $result = [
      "result" => true, "amount" => 0, "action" => CombatAction::ACTION_SKILL_SPECIAL, "name" => $skill->skill->name,
      "character1" => $character1, "character2" => $target,
    ];
    $effect = new CharacterEffect([
      "id" => "skill{$skill->skill->id}Effect",
      "type" => $skill->skill->type,
      "stat" => ((in_array($skill->skill->type, SkillSpecial::NO_STAT_TYPES, true)) ? null : $skill->skill->stat),
      "value" => $skill->value,
      "source" => CharacterEffect::SOURCE_SKILL,
      "duration" => $skill->skill->duration,
    ]);
    $target->addEffect($effect);
    $this->log->log($result);
    $skill->resetCooldown();
  }
  
  /**
   * Heal a character
   */
  public function heal(Character $healer, Character $patient): void {
    $result = [];
    $result["result"] = $this->successCalculator->hasHealed($healer);
    $amount = ($result["result"]) ? (int) ($healer->intelligence / 2) : 0;
    $result["amount"] = Numbers::range($amount, 0, $patient->maxHitpoints - $patient->hitpoints);
    if($result["amount"] > 0) {
      $patient->heal($result["amount"]);
    }
    $result["action"] = CombatAction::ACTION_HEALING;
    $result["name"] = "";
    $result["character1"] = $healer;
    $result["character2"] = $patient;
    $this->log->log($result);
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
            "action" => CombatAction::ACTION_POISON, "result" => true, "amount" => $effect->value,
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