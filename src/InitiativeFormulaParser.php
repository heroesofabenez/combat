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
  
  public function calculateInitiative(string $formula, Character $character): int {
    $result = 0;
    $stats = [
      "INT" => $character->intelligence, "DEX" => $character->dexterity, "STR" => $character->strength, "CON" => $character->constitution,
      "CHAR" => $character->charisma,
    ];
    $formula = str_replace(array_keys($stats), array_values($stats), $formula);
    preg_match_all("/^([1-9]+)d([1-9]+)/", $formula, $dices);
    for($i = 1; $i <= (int) $dices[1][0]; $i++) {
      $result += rand(1, (int) $dices[2][0]);
    }
    preg_match_all("/\+([0-9]+)\/([0-9]+)/", $formula, $ammendum);
    $result += (int) $ammendum[1][0] / (int) $ammendum[2][0];
    return (int) $result;
  }  
}
?>