<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ISuccessCalculator
 *
 * @author Jakub Konečný
 */
interface ISuccessCalculator {
  public function calculateHitChance(Character $character1, Character $character2, ?CharacterAttackSkill $skill = NULL): int;
  public function calculateHealingSuccessChance(Character $healer): int;
  public function hasHit(int $hitChance): bool;
}
?>