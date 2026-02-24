<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * CharacterEffectsProvider
 *
 * @author Jakub Konečný
 */
interface CharacterEffectsProvider
{
    /** @return CharacterEffect[] */
    public function getCombatEffects(): array;
}
