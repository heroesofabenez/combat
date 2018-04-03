<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * DummyInitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
final class DummyInitiativeFormulaParser implements IInitiativeFormulaParser {
  use \Nette\SmartObject;
  
  public function calculateInitiative(string $formula, Character $character): int {
    return 0;
  }
}
?>