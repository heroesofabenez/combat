<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

final class WeaponTest extends \Tester\TestCase {
  public function testIsRanged() {
    $weaponStats = [
      "id" => 1, "name" => "Weapon", "slot" => Equipment::SLOT_WEAPON, "strength" => 1, "worn" => true,
    ];
    foreach(Weapon::MELEE_TYPES as $meleeWeapon) {
      $weaponStats["type"] = $meleeWeapon;
      $weapon = new Weapon($weaponStats);
      Assert::false($weapon->ranged);
    }
    foreach(Weapon::RANGED_TYPES as $rangedWeapon) {
      $weaponStats["type"] = $rangedWeapon;
      $weapon = new Weapon($weaponStats);
      Assert::true($weapon->ranged);
    }
  }
}

$test = new WeaponTest();
$test->run();
?>