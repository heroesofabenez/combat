<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class EquipmentTest extends \Tester\TestCase {
  public function testGetCombatEffects() {
    $equipment = new Equipment([
      "id" => 1, "name" => "Novice Helmet", "slot" => Equipment::SLOT_HELMET,
      "strength" => 1, "worn" => false,
    ]);
    Assert::count(0, $equipment->getCombatEffects());
    $equipment->worn = true;
    Assert::count(1, $equipment->getCombatEffects());
  }

  public function testDurability() {
    $data = [
      "id" => 1, "name" => "Novice Helmet", "slot" => Equipment::SLOT_HELMET,
      "strength" => 20, "worn" => true, "maxDurability" => 10,
    ];
    $equipment = new Equipment($data);
    Assert::same($equipment->maxDurability, $equipment->durability);
    $data["durability"] = 0;
    $equipment = new Equipment($data);
    Assert::same(0, $equipment->durability);
    $equipment->durability = 20;
    Assert::same($equipment->maxDurability, $equipment->durability);
    Assert::same($equipment->rawStrength, $equipment->strength);
    Assert::same($equipment->rawStrength, $equipment->getCombatEffects()[0]->value);
    $equipment->durability = (int) ($equipment->maxDurability * 0.7 - 1);
    Assert::same((int) ($equipment->rawStrength * 0.75), $equipment->strength);
    Assert::same((int) ($equipment->rawStrength * 0.75), $equipment->getCombatEffects()[0]->value);
    $equipment->durability = (int) ($equipment->maxDurability / 2 - 1);
    Assert::same($equipment->rawStrength / 2, $equipment->strength);
    Assert::same($equipment->rawStrength / 2, $equipment->getCombatEffects()[0]->value);
    $equipment->durability = (int) ($equipment->maxDurability / 4 - 1);
    Assert::same($equipment->rawStrength / 4, $equipment->strength);
    Assert::same($equipment->rawStrength / 4, $equipment->getCombatEffects()[0]->value);
    $equipment->durability = (int) ($equipment->maxDurability / 10 - 1);
    Assert::same(0, $equipment->strength);
    Assert::same(0, $equipment->getCombatEffects()[0]->value);
  }
}

$test = new EquipmentTest();
$test->run();
?>