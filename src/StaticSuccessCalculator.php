<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * StaticSuccessCalculator
 *
 * @author Jakub Konečný
 */
final class StaticSuccessCalculator implements ISuccessCalculator {
  public function calculateHitChance(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): int {
    return static::MAX_HIT_CHANCE;
  }
  
  public function calculateHealingSuccessChance(Character $healer): int {
    return static::MAX_HIT_CHANCE;
  }
  
  public function hasHit(int $hitChance): bool {
    return true;
  }
}
?>