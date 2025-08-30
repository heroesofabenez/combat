<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ISuccessCalculator
 *
 * @author Jakub Konečný
 */
interface ISuccessCalculator
{
    public function hasHit(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): bool;
    public function hasHealed(Character $healer): bool;
}
