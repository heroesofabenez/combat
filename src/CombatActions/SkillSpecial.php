<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CharacterSpecialSkill;
use HeroesofAbenez\Combat\ICombatAction;
use HeroesofAbenez\Combat\SkillSpecial as Skill;
use HeroesofAbenez\Combat\NotImplementedException;
use HeroesofAbenez\Combat\CharacterEffect;

final class SkillSpecial implements ICombatAction {
  use \Nette\SmartObject;

  public const ACTION_NAME = "skill_special";

  public function getName(): string {
    return static::ACTION_NAME;
  }

  public function getPriority(): int {
    return 1000;
  }

  public function shouldUse(CombatBase $combat, Character $character): bool {
    $attackTarget = $combat->selectAttackTarget($character);
    if($attackTarget === null) {
      return false;
    }
    if(count($character->usableSkills) < 1) {
      return false;
    }
    return ($character->usableSkills[0] instanceof CharacterSpecialSkill);
  }

  protected function doSingleTarget(Character $character1, Character $target, CharacterSpecialSkill $skill, CombatBase $combat): void {
    $result = [
      "result" => true, "amount" => 0, "action" => $this->getName(), "name" => $skill->skill->name,
      "character1" => $character1, "character2" => $target,
    ];
    $effect = new CharacterEffect([
      "id" => "skill{$skill->skill->id}Effect",
      "type" => $skill->skill->type,
      "stat" => ((in_array($skill->skill->type, Skill::NO_STAT_TYPES, true)) ? null : $skill->skill->stat),
      "value" => $skill->value,
      "valueAbsolute" => false,
      "duration" => $skill->skill->duration,
    ]);
    $target->effects[] = $effect;
    $combat->log->log($result);
    $skill->resetCooldown();
  }

  /**
   * @throws NotImplementedException
   */
  public function do(CombatBase $combat, Character $character): void {
    /** @var CharacterSpecialSkill $skill */
    $skill = $character->usableSkills[0];
    $targets = [];
    switch($skill->skill->target) {
      case Skill::TARGET_ENEMY:
        $targets[] = $combat->selectAttackTarget($character);
        break;
      case Skill::TARGET_SELF:
        $targets[] = $character;
        break;
      case Skill::TARGET_PARTY:
        $targets = $combat->getTeam($character)->toArray();
        break;
      case Skill::TARGET_ENEMY_PARTY:
        $targets = $combat->getEnemyTeam($character)->toArray();
        break;
      default:
        throw new NotImplementedException("Target {$skill->skill->target} for special skills is not implemented.");
    }
    foreach($targets as $target) {
      $this->doSingleTarget($character, $target, $skill, $combat);
    }
  }
}
?>