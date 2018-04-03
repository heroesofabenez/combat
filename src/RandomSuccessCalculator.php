<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;

/**
 * RandomSuccessCalculator
 *
 * @author Jakub Konečný
 */
final class RandomSuccessCalculator implements ISuccessCalculator {
  public function calculateHitChance(Character $character1, Character $character2, ?CharacterAttackSkill $skill = NULL): int {
    $hitRate = $character1->hit;
    $dodgeRate = $character2->dodge;
    if(!is_null($skill)) {
      $hitRate = $hitRate / 100 * $skill->hitRate;
    }
    return Numbers::range((int) ($hitRate - $dodgeRate), 15, 100);
  }
  
  public function calculateHealingSuccessChance(Character $healer): int {
    return $healer->intelligence * (int) round($healer->level / 5) + 30;
  }
  
  public function hasHit(int $hitChance): bool {
    $roll = rand(0, 100);
    return ($roll <= $hitChance);
  }
}
?>