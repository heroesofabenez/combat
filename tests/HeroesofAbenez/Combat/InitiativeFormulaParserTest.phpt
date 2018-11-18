<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class InitiativeFormulaParserTest extends \Tester\TestCase {
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d4+DEX/4", "strength" => 10,
      "dexterity" => 12, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testCalculateInitiative() {
    $character = $this->generateCharacter(1);
    $parser = new InitiativeFormulaParser();
    for($i = 1; $i <= 10; $i++) {
      $initiative = $parser->calculateInitiative($character);
      Assert::true($initiative >= 4);
      Assert::true($initiative <= 8);
    }
  }
}

$test = new InitiativeFormulaParserTest();
$test->run();
?>