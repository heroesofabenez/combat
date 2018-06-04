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
  
  public function calculateHitChance(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): int;
  public function calculateHealingSuccessChance(Character $healer): int;
  public function hasHit(int $hitChance): bool;
}
?>