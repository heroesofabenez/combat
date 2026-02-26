<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ActionSelector
 *
 * @author Jakub Konečný
 */
final class ActionSelector implements CombatActionSelector
{
    public function chooseAction(CombatBase $combat, Character $character): ?CombatAction
    {
        return null;
    }
}
