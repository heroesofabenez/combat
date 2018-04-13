<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class EquipmentTest extends \Tester\TestCase {
  public function testGetCombatEffects() {
    $equipment = new Equipment([
      "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Equipment::TYPE_SWORD,
      "strength" => 1, "worn" => false,
    ]);
    Assert::count(0, $equipment->getCombatEffects());
    $equipment->worn = true;
    Assert::count(1, $equipment->getCombatEffects());
  }
}

$test = new EquipmentTest();
$test->run();
?>