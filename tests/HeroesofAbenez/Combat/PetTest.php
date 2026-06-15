<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("Pet")]
final class PetTest extends \MyTester\TestCase
{
    public function testGetCombatEffects(): void
    {
        $pet = new Pet([
            "id" => 1, "deployed" => false, "bonusStat" => Character::STAT_STRENGTH, "bonusValue" => 10,
        ]);
        $this->assertCount(0, $pet->getCombatEffects());
        $pet->deployed = true;
        $this->assertCount(1, $pet->getCombatEffects());
    }
}
