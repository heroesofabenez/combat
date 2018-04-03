<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * StaticSuccessCalculator
 *
 * @author Jakub Konečný
 */
final class StaticSuccessCalculator implements ISuccessCalculator {
  public function calculateHitChance(Character $character1, Character $character2, ?CharacterAttackSkill $skill = NULL): int {
    return 100;
  }
  
  public function calculateHealingSuccessChance(Character $healer): int {
    return 100;
  }
  
  public function hasHit(int $hitChance): bool {
    return true;
  }
}
?>