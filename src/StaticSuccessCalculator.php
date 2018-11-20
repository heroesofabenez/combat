<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * StaticSuccessCalculator
 *
 * @author Jakub Konečný
 */
final class StaticSuccessCalculator implements ISuccessCalculator {
  use \Nette\SmartObject;

  public function hasHit(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): bool {
    return true;
  }
  
  public function hasHealed(Character $healer): bool {
    return true;
  }
}
?>