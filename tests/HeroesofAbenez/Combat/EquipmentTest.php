<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("Equipment")]
final class EquipmentTest extends \MyTester\TestCase
{
    public function testGetCombatEffects(): void
    {
        $equipment = new Equipment([
            "id" => 1, "name" => "Novice Helmet", "slot" => Equipment::SLOT_HELMET,
            "strength" => 1, "worn" => false,
        ]);
        $this->assertCount(0, $equipment->getCombatEffects());
        $equipment->worn = true;
        $this->assertCount(1, $equipment->getCombatEffects());
    }

    public function testDurability(): void
    {
        $data = [
            "id" => 1, "name" => "Novice Helmet", "slot" => Equipment::SLOT_HELMET,
            "strength" => 20, "worn" => true, "maxDurability" => 10,
        ];
        $equipment = new Equipment($data);
        $this->assertSame($equipment->maxDurability, $equipment->durability);
        $data["durability"] = 0;
        $equipment = new Equipment($data);
        $this->assertSame(0, $equipment->durability);
        $equipment->durability = 20;
        $this->assertSame($equipment->maxDurability, $equipment->durability);
        $this->assertSame($equipment->rawStrength, $equipment->strength);
        $this->assertSame($equipment->rawStrength, $equipment->getCombatEffects()[0]->value);
        $equipment->durability = (int) ($equipment->maxDurability * 0.7 - 1);
        $this->assertSame((int) ($equipment->rawStrength * 0.75), $equipment->strength);
        $this->assertSame((int) ($equipment->rawStrength * 0.75), $equipment->getCombatEffects()[0]->value);
        $equipment->durability = (int) ($equipment->maxDurability / 2 - 1);
        $this->assertSame($equipment->rawStrength / 2, $equipment->strength);
        $this->assertSame($equipment->rawStrength / 2, $equipment->getCombatEffects()[0]->value);
        $equipment->durability = (int) ($equipment->maxDurability / 4 - 1);
        $this->assertSame($equipment->rawStrength / 4, $equipment->strength);
        $this->assertSame($equipment->rawStrength / 4, $equipment->getCombatEffects()[0]->value);
        $equipment->durability = (int) ($equipment->maxDurability / 10 - 1);
        $this->assertSame(0, $equipment->strength);
        $this->assertSame(0, $equipment->getCombatEffects()[0]->value);
    }
}
