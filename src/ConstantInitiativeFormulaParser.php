<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ConstantInitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
final class ConstantInitiativeFormulaParser implements IInitiativeFormulaParser {
  /** @var int */
  protected $initiative;
  
  public function __construct(int $initiative) {
    $this->initiative = $initiative;
  }
  
  public function calculateInitiative(string $formula, Character $character): int {
    return $this->initiative;
  }
}
?>