<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class CharacterEffectTest extends \Tester\TestCase {
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testInitiativeEffect(): void {
    $character = $this->generateCharacter(1);
    $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(1);
    Assert::same(1, $character->initiative);
    Assert::same(1, $character->initiativeBase);
    $effect = new CharacterEffect([
      "id" => "equipment1bonusEffect",
      "type" => "buff",
      "stat" => Character::STAT_INITIATIVE,
      "value" => 10,
      "valueAbsolute" => true,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ]);
    $character->effects[] = $effect;
    Assert::same(11, $character->initiative);
    Assert::same(1, $character->initiativeBase);
    $character->effects->removeByFilter(["id" => $effect->id]);
    Assert::same(1, $character->initiative);
    Assert::same(1, $character->initiativeBase);
  }
  
  public function testHitpointsEffect(): void {
    $character = $this->generateCharacter(1);
    $baseHitpoints = $character->constitution * Character::HITPOINTS_PER_CONSTITUTION;
    Assert::same($baseHitpoints, $character->maxHitpointsBase);
    Assert::same($baseHitpoints, $character->maxHitpoints);
    Assert::same($baseHitpoints, $character->hitpoints);
    $effect = new CharacterEffect([
      "id" => "equipment1bonusEffect",
      "type" => "buff",
      "stat" => Character::STAT_MAX_HITPOINTS,
      "value" => 10,
      "valueAbsolute" => true,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ]);
    $character->effects[] = $effect;
    Assert::same($baseHitpoints, $character->maxHitpointsBase);
    Assert::same(60, $character->maxHitpoints);
    Assert::same(60, $character->hitpoints);
    $character->effects->removeByFilter(["id" => "equipment1bonusEffect"]);
    Assert::same($baseHitpoints, $character->maxHitpointsBase);
    Assert::same($baseHitpoints, $character->maxHitpoints);
    Assert::same($baseHitpoints, $character->hitpoints);
  }
}

$test = new CharacterEffectTest();
$test->run();
?>