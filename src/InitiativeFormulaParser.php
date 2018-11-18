<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * InitiativeFormulaParser
 *
 * @author Jakub Konečný
 */
final class InitiativeFormulaParser implements IInitiativeFormulaParser {
  use \Nette\SmartObject;
  
  public function calculateInitiative(Character $character): int {
    $result = 0;
    $formula = $character->initiativeFormula;
    $stats = [
      "INT" => $character->intelligence, "DEX" => $character->dexterity, "STR" => $character->strength, "CON" => $character->constitution,
      "CHAR" => $character->charisma,
    ];
    $formula = str_replace(array_keys($stats), array_values($stats), $formula);
    preg_match("/^([1-9]+)d([1-9]+)/", $formula, $dices);
    for($i = 1; $i <= (int) $dices[1]; $i++) {
      $result += rand(1, (int) $dices[2]);
    }
    preg_match("/\+([0-9]+)\/([0-9]+)/", $formula, $ammendum);
    $result += (int) $ammendum[1] / (int) $ammendum[2];
    return (int) $result;
  }  
}
?>