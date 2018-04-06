<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class CharacterTest extends \Tester\TestCase {
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testAddAndRemoveEffect() {
    $character = $this->generateCharacter(1);
    Assert::count(0, $character->effects);
    Assert::same(5, $character->damage);
    $character->addEffect(new CharacterEffect([
      "id" => "equipment1bonusEffect",
      "type" => "buff",
      "stat" => SkillSpecial::STAT_DAMAGE,
      "value" => 10,
      "source" => CharacterEffect::SOURCE_EQUIPMENT,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ]));
    Assert::count(1, $character->effects);
    Assert::same(15, $character->damage);
    Assert::exception(function() use($character) {
      $character->removeEffect("abc");
    }, \OutOfBoundsException::class);
    $character->removeEffect("equipment1bonusEffect");
    Assert::count(0, $character->effects);
    Assert::same(5, $character->damage);
  }
  
  public function testInitiativeFormulaParser() {
    $character = $this->generateCharacter(1);
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