<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class CharacterTest extends \Tester\TestCase {
  public function testInitiativeFormulaParser() {
    $stats = [
      "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    $character = new Character($stats);
    Assert::type(InitiativeFormulaParser::class, $character->initiativeFormulaParser);
    $character->calculateInitiative();
    Assert::notEqual(0, $character->initiative);
    $character->initiativeFormulaParser = new DummyInitiativeFormulaParser();
    $character->calculateInitiative();
    Assert::equal(0, $character->initiative);
  }
}

$test = new CharacterTest();
$test->run();
?>