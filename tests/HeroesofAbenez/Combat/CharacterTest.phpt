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
      "id" => 1, "deployed" => false, "bonusStat" => Character::STAT_STRENGTH, "bonusValue" => 10,
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
      "id" => 1, "name" => "Novice Helmet", "slot" => Equipment::SLOT_HELMET,
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
      "id" => 1, "deployed" => true, "bonusStat" => Character::STAT_STRENGTH, "bonusValue" => 10,
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
    $character->effects[] = new CharacterEffect([
      "id" => "equipment1bonusEffect",
      "type" => "buff",
      "stat" => Character::STAT_DAMAGE,
      "value" => 10,
      "valueAbsolute" => true,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ]);
    Assert::count(1, $character->effects);
    Assert::same(15, $character->damage);
    $character->effects->removeByFilter(["id" => "equipment1bonusEffect"]);
    Assert::count(0, $character->effects);
    Assert::same(5, $character->damage);
  }
  
  public function testInitiativeFormulaParser() {
    $character = $this->generateCharacter(1);
    Assert::type(InitiativeFormulaParser::class, $character->initiativeFormulaParser);
    Assert::notEqual(0, $character->initiative);
    $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(0);
    Assert::equal(0, $character->initiative);
  }
  
  public function testDebuffsCap() {
    $character = $this->generateCharacter(1);
    $effect = new CharacterEffect([
      "id" => "skillEffect", "type" => SkillSpecial::TYPE_DEBUFF, "valueAbsolute" => false,
      "value" => 1000, "duration" => 1, "stat" => "constitution",
    ]);
    $character->effects[] = $effect;
    Assert::same(2, $character->constitution);
  }

  public function testDamageStat() {
    $stats = [
      "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    $equipment = [
      new Weapon([
        "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Weapon::TYPE_SWORD,
        "strength" => 1, "worn" => true
      ]),
      new Weapon([
        "id" => 2, "name" => "Novice Staff", "slot" => Equipment::SLOT_WEAPON, "type" => Weapon::TYPE_STAFF,
        "strength" => 1, "worn" => true
      ])
    ];
    $character = new Character($stats, $equipment);
    Assert::same(Character::STAT_STRENGTH, $character->damageStat());
    $equipment[0]->worn = false;
    Assert::same(Character::STAT_INTELLIGENCE, $character->damageStat());
    $equipment[1]->worn = false;
    Assert::same(Character::STAT_STRENGTH, $character->damageStat());
  }

  public function testStatus() {
    $character = $this->generateCharacter(1);
    Assert::false($character->hasStatus(Character::STATUS_STUNNED));
    $character->addStatus(Character::STATUS_STUNNED);
    Assert::true($character->hasStatus(Character::STATUS_STUNNED));
    $character->removeStatus(Character::STATUS_STUNNED);
    Assert::false($character->hasStatus(Character::STATUS_STUNNED));
    $character->addStatus(Character::STATUS_POISONED, 5);
    Assert::true($character->hasStatus(Character::STATUS_POISONED));
    $character->addStatus(Character::STATUS_POISONED, 0);
    Assert::false($character->hasStatus(Character::STATUS_POISONED));
    $character->removeStatus(Character::STATUS_POISONED);
    Assert::false($character->hasStatus(Character::STATUS_POISONED));
  }
}

$test = new CharacterTest();
$test->run();
?>