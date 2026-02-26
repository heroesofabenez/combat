<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 */
interface CombatActionSelector
{
    public function chooseAction(CombatBase $combat, Character $character): ?CombatAction;
}
