<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;

/**
 * RandomSuccessCalculator
 *
 * @author Jakub Konečný
 */
final class RandomSuccessCalculator implements ISuccessCalculator
{
    public const int MAX_HIT_CHANCE = 100;
    public const int MIN_HIT_CHANCE = 15;

    public function hasHit(Character $character1, Character $character2, ?CharacterAttackSkill $skill = null): bool
    {
        if (!$character2->canDefend()) {
            return true;
        }
        $hitRate = $character1->hit;
        $dodgeRate = $character2->dodge;
        if ($skill !== null) {
            $hitRate = $hitRate / 100 * $skill->hitRate;
        }
        $hitChance = Numbers::clamp((int) ($hitRate - $dodgeRate), self::MIN_HIT_CHANCE, self::MAX_HIT_CHANCE);
        $roll = rand(0, 100);
        return ($roll <= $hitChance);
    }

    public function hasHealed(Character $healer): bool
    {
        $chance = $healer->intelligence * (int) round($healer->level / 5) + 30;
        $roll = rand(0, 100);
        return ($roll <= $chance);
    }
}
