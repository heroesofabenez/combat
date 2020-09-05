<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ConstantInitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
final class ConstantInitiativeFormulaParser implements IInitiativeFormulaParser {
  use \Nette\SmartObject;

  protected int $initiative;
  
  public function __construct(int $initiative) {
    $this->initiative = $initiative;
  }
  
  public function calculateInitiative(Character $character): int {
    return $this->initiative;
  }
}
?>