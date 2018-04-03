<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * IInitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
interface IInitiativeFormulaParser {
  public function calculateInitiative(string $formula, Character $character): int;
}
?>