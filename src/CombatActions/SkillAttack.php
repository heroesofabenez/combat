<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CharacterAttackSkill;
use HeroesofAbenez\Combat\ICombatAction;
use HeroesofAbenez\Combat\SkillAttack as Skill;
use HeroesofAbenez\Combat\NotImplementedException;
use Nexendrie\Utils\Numbers;

final class SkillAttack implements ICombatAction {
  use \Nette\SmartObject;

  public const ACTION_NAME = "skill_attack";

  public function getName(): string {
    return static::ACTION_NAME;
  }

  public function getPriority(): int {
    return 1001;
  }

  public function shouldUse(CombatBase $combat, Character $character): bool {
    $attackTarget = $combat->selectAttackTarget($character);
    if($attackTarget === null) {
      return false;
    }
    if(count($character->usableSkills) < 1) {
      return false;
    }
    return ($character->usableSkills[0] instanceof CharacterAttackSkill);
  }

  protected function doSingleAttack(Character $attacker, Character $defender, CharacterAttackSkill $skill, CombatBase $combat): void {
    $result = [];
    $result["result"] = $combat->successCalculator->hasHit($attacker, $defender, $skill);
    $result["amount"] = 0;
    if($result["result"]) {
      $amount = (int) (($attacker->damage - $defender->defense) / 100 * $skill->damage);
      $result["amount"] = Numbers::range($amount, 0, $defender->hitpoints);
    }
    if($result["amount"] > 0) {
      $defender->harm($result["amount"]);
    }
    $result["action"] = $this->getName();
    $result["name"] = $skill->skill->name;
    $result["character1"] = $attacker;
    $result["character2"] = $defender;
    $combat->logDamage($attacker, $result["amount"]);
    $combat->log->log($result);
    $skill->resetCooldown();
  }

  /**
   * @throws NotImplementedException
   */
  public function do(CombatBase $combat, Character $character): void {
    /** @var CharacterAttackSkill $skill */
    $skill = $character->usableSkills[0];
    $targets = [];
    /** @var Character $primaryTarget */
    $primaryTarget = $combat->selectAttackTarget($character);
    switch($skill->skill->target) {
      case Skill::TARGET_SINGLE:
        $targets[] = $primaryTarget;
        break;
      case Skill::TARGET_ROW:
        $targets = $combat->getTeam($primaryTarget)->getItems(["positionRow" => $primaryTarget->positionRow]);
        break;
      case Skill::TARGET_COLUMN:
        $targets = $combat->getTeam($primaryTarget)->getItems(["positionColumn" => $primaryTarget->positionColumn]);
        break;
      default:
        throw new NotImplementedException("Target {$skill->skill->target} for attack skills is not implemented.");
    }
    /** @var Character $target */
    foreach($targets as $target) {
      for($i = 1; $i <= $skill->skill->strikes; $i++) {
        if($target->hitpoints > 0) {
          $this->doSingleAttack($character, $target, $skill, $combat);
        }
      }
    }
  }
}
?>