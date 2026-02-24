<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ConstantInitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
final readonly class ConstantInitiativeFormulaParser implements InitiativeFormulaParser
{
    public function __construct(private int $initiative)
    {
    }

    public function calculateInitiative(Character $character): int
    {
        return $this->initiative;
    }
}
