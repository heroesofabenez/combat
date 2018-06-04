<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Constants;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 */
final class CombatActionSelector implements ICombatActionSelector {
  use \Nette\SmartObject;
  
  public function getAllowedActions(): array {
    $allowedActions = Constants::getConstantsValues(CombatAction::class, "ACTION_");
    return array_values(array_filter($allowedActions, function(string $value) {
      return ($value !== CombatAction::ACTION_POISON);
    }));
  }
  
  public function chooseAction(CombatBase $combat, Character $character): ?string {
    if($character->hitpoints < 1) {
      return NULL;
    } elseif(in_array($character, $combat->findHealers()->toArray(), true) AND !is_null($combat->selectHealingTarget($character))) {
      return CombatAction::ACTION_HEALING;
    }
    $attackTarget = $combat->selectAttackTarget($character);
    if(is_null($attackTarget)) {
      return NULL;
    }
    if(count($character->usableSkills) > 0) {
      $skill = $character->usableSkills[0];
      if($skill instanceof CharacterAttackSkill) {
        return CombatAction::ACTION_SKILL_ATTACK;
      } elseif($skill instanceof  CharacterSpecialSkill) {
        return CombatAction::ACTION_SKILL_SPECIAL;
      }
    }
    return CombatAction::ACTION_ATTACK;
  }
}
?>