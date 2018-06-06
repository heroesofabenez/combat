<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

final class WeaponTest extends \Tester\TestCase {
  public function testIsRanged() {
    $meleeWeapons = [
      Weapon::TYPE_SWORD, Weapon::TYPE_AXE, Weapon::TYPE_CLUB, Weapon::TYPE_DAGGER, Weapon::TYPE_SPEAR,
    ];
    $rangedWeapons = [
      Weapon::TYPE_STAFF, Weapon::TYPE_BOW, Weapon::TYPE_CROSSBOW, Weapon::TYPE_THROWING_KNIFE,
    ];
    $weaponStats = [
      "id" => 1, "name" => "Weapon", "slot" => Equipment::SLOT_WEAPON, "strength" => 1, "worn" => true,
    ];
    foreach($meleeWeapons as $meleeWeapon) {
      $weaponStats["type"] = $meleeWeapon;
      $weapon = new Weapon($weaponStats);
      Assert::false($weapon->ranged);
    }
    foreach($rangedWeapons as $rangedWeapon) {
      $weaponStats["type"] = $rangedWeapon;
      $weapon = new Weapon($weaponStats);
      Assert::true($weapon->ranged);
    }
  }
}

$test = new WeaponTest();
$test->run();
?>