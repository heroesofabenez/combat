<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ISuccessCalculator
 *
 * @author Jakub Konečný
 */
interface ISuccessCalculator {
  public const MAX_HIT_CHANCE = 100;
  public const MIN_HIT_CHANCE = 0;
  
  public function hasHit(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): bool;
  public function hasHealed(Character $healer): bool;
}
?>