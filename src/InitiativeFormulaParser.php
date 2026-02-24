<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * InitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
interface InitiativeFormulaParser
{
    public function calculateInitiative(Character $character): int;
}
