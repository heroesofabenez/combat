<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class EquipmentTest extends \Tester\TestCase {
  public function testToCombatEffect() {
    $equipmentStats = [
      "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Equipment::TYPE_SWORD,
      "strength" => 1, "worn" => false,
    ];
    Assert::null((new Equipment($equipmentStats))->toCombatEffect());
    $equipmentStats["worn"] = true;
    Assert::type(CharacterEffect::class, (new Equipment($equipmentStats))->toCombatEffect());
  }
}

$test = new EquipmentTest();
$test->run();
?>