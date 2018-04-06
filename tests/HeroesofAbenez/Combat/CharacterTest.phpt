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
  
  public function testGetActivePet() {
    $stats = [
      "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    $petStats = [
      "id" => 1, "deployed" => false, "bonusStat" => Pet::STAT_STRENGTH, "bonusValue" => 10,
    ];
    $pet = new Pet($petStats);
    $character = new Character($stats, [], [$pet]);
    Assert::null($character->activePet);
    $pet->deployed = true;
    Assert::same(1, $character->activePet);
  }
  
  public function testGetItem() {
    $stats = [
      "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    $equipment = new Equipment([
      "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Equipment::TYPE_SWORD,
      "strength" => 1, "worn" => true
    ]);
    $character = new Character($stats, [$equipment]);
    Assert::type(Equipment::class, $character->getItem(1));
    Assert::exception(function() use($character) {
      $character->getItem(0);
    }, \OutOfBoundsException::class);
  }
  
  public function testGetPet() {
    $stats = [
      "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    $pet = new Pet([
      "id" => 1, "deployed" => true, "bonusStat" => Pet::STAT_STRENGTH, "bonusValue" => 10,
    ]);
    $character = new Character($stats, [], [$pet]);
    Assert::type(Pet::class, $character->getPet(1));
    Assert::exception(function() use($character) {
      $character->getPet(0);
    }, \OutOfBoundsException::class);
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
    $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(0);
    $character->calculateInitiative();
    Assert::equal(0, $character->initiative);
  }
}

$test = new CharacterTest();
$test->run();
?>