<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("Weapon")]
final class WeaponTest extends \MyTester\TestCase
{
    public function testIsRanged(): void
    {
        $weaponStats = [
            "id" => 1, "name" => "Weapon", "slot" => Equipment::SLOT_WEAPON, "strength" => 1, "worn" => true,
        ];
        foreach (Weapon::MELEE_TYPES as $meleeWeapon) {
            $weaponStats["type"] = $meleeWeapon;
            $weapon = new Weapon($weaponStats);
            $this->assertFalse($weapon->ranged);
        }
        foreach (Weapon::RANGED_TYPES as $rangedWeapon) {
            $weaponStats["type"] = $rangedWeapon;
            $weapon = new Weapon($weaponStats);
            $this->assertTrue($weapon->ranged);
        }
    }
}
