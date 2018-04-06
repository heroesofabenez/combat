<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class EquipmentTest extends \Tester\TestCase {
  public function testToCombatEffect() {
    $equipment = new Equipment([
      "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Equipment::TYPE_SWORD,
      "strength" => 1, "worn" => false,
    ]);
    Assert::null($equipment->toCombatEffect());
    $equipment->worn = true;
    Assert::type(CharacterEffect::class, $equipment->toCombatEffect());
  }
}

$test = new EquipmentTest();
$test->run();
?>