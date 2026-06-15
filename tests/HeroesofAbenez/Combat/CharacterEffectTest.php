<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("CharacterEffect")]
final class CharacterEffectTest extends \MyTester\TestCase
{
    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testInitiativeEffect(): void
    {
        $character = $this->generateCharacter(1);
        $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(1);
        $this->assertSame(1, $character->initiative);
        $this->assertSame(1, $character->initiativeBase);
        $effect = new CharacterEffect([
            "id" => "equipment1bonusEffect",
            "type" => "buff",
            "stat" => Character::STAT_INITIATIVE,
            "value" => 10,
            "valueAbsolute" => true,
            "duration" => CharacterEffectDuration::Combat,
        ]);
        $character->effects[] = $effect;
        $this->assertSame(11, $character->initiative);
        $this->assertSame(1, $character->initiativeBase);
        $character->effects->removeByFilter(["id" => $effect->id]);
        $this->assertSame(1, $character->initiative);
        $this->assertSame(1, $character->initiativeBase);
    }

    public function testHitpointsEffect(): void
    {
        $character = $this->generateCharacter(1);
        $baseHitpoints = $character->constitution * Character::HITPOINTS_PER_CONSTITUTION;
        $this->assertSame($baseHitpoints, $character->maxHitpointsBase);
        $this->assertSame($baseHitpoints, $character->maxHitpoints);
        $this->assertSame($baseHitpoints, $character->hitpoints);
        $effect = new CharacterEffect([
            "id" => "equipment1bonusEffect",
            "type" => "buff",
            "stat" => Character::STAT_MAX_HITPOINTS,
            "value" => 10,
            "valueAbsolute" => true,
            "duration" => CharacterEffectDuration::Combat,
        ]);
        $character->effects[] = $effect;
        $this->assertSame($baseHitpoints, $character->maxHitpointsBase);
        $this->assertSame(60, $character->maxHitpoints);
        $this->assertSame(60, $character->hitpoints);
        $character->effects->removeByFilter(["id" => "equipment1bonusEffect"]);
        $this->assertSame($baseHitpoints, $character->maxHitpointsBase);
        $this->assertSame($baseHitpoints, $character->maxHitpoints);
        $this->assertSame($baseHitpoints, $character->hitpoints);
    }
}
