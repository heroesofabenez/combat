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
  use \Nette\SmartObject;

  public function hasHit(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): bool {
    if($character2->hasStatus(Character::STATUS_STUNNED)) {
      return true;
    }
    $hitRate = $character1->hit;
    $dodgeRate = $character2->dodge;
    if($skill !== null) {
      $hitRate = $hitRate / 100 * $skill->hitRate;
    }
    $hitChance = Numbers::range((int) ($hitRate - $dodgeRate), 15, static::MAX_HIT_CHANCE);
    $roll = rand(0, 100);
    return ($roll <= $hitChance);
  }
  
  public function hasHealed(Character $healer): bool {
    $chance = $healer->intelligence * (int) round($healer->level / 5) + 30;
    $roll = rand(0, 100);
    return ($roll <= $chance);
  }
}
?>