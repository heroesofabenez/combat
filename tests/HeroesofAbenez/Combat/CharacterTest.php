<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("Character")]
final class CharacterTest extends \MyTester\TestCase
{
    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testGetActivePet(): void
    {
        $stats = [
            "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        $petStats = [
            "id" => 1, "deployed" => false, "bonusStat" => Character::STAT_STRENGTH, "bonusValue" => 10,
        ];
        $pet = new Pet($petStats);
        $character = new Character($stats, [], [$pet]);
        $this->assertNull($character->activePet);
        $pet->deployed = true;
        $this->assertSame(1, $character->activePet);
    }

    public function testAddAndRemoveEffect(): void
    {
        $character = $this->generateCharacter(1);
        $this->assertCount(0, $character->effects);
        $this->assertSame(5, $character->damage);
        $character->effects[] = new CharacterEffect([
            "id" => "equipment1bonusEffect",
            "type" => "buff",
            "stat" => Character::STAT_DAMAGE,
            "value" => 10,
            "valueAbsolute" => true,
            "duration" => CharacterEffectDuration::Combat,
        ]);
        $this->assertCount(1, $character->effects);
        $this->assertSame(15, $character->damage);
        $character->effects->removeByFilter(["id" => "equipment1bonusEffect"]);
        $this->assertCount(0, $character->effects);
        $this->assertSame(5, $character->damage);
    }

    public function testInitiativeFormulaParser(): void
    {
        $character = $this->generateCharacter(1);
        $this->assertType(DefaultInitiativeFormulaParser::class, $character->initiativeFormulaParser);
        $this->assertNotSame(0, $character->initiative);
        $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(0);
        $this->assertSame(0, $character->initiative);
    }

    public function testDebuffsCap(): void
    {
        $character = $this->generateCharacter(1);
        $effect = new CharacterEffect([
            "id" => "skillEffect", "type" => SkillSpecial::TYPE_DEBUFF, "valueAbsolute" => false,
            "value" => 1000, "duration" => 1, "stat" => "constitution",
        ]);
        $character->effects[] = $effect;
        $this->assertSame(2, $character->constitution);
    }

    public function testDamageStat(): void
    {
        $stats = [
            "id" => 1, "name" => "Player 1", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        $equipment = [
            new Weapon([
                "id" => 1, "name" => "Novice Sword", "slot" => Equipment::SLOT_WEAPON, "type" => Weapon::TYPE_SWORD,
                "strength" => 1, "worn" => true
            ]),
            new Weapon([
                "id" => 2, "name" => "Novice Staff", "slot" => Equipment::SLOT_WEAPON, "type" => Weapon::TYPE_STAFF,
                "strength" => 1, "worn" => true
            ])
        ];
        $character = new Character($stats, $equipment);
        $this->assertSame(Character::STAT_STRENGTH, $character->damageStat());
        $equipment[0]->worn = false;
        $this->assertSame(Character::STAT_INTELLIGENCE, $character->damageStat());
        $equipment[1]->worn = false;
        $this->assertSame(Character::STAT_STRENGTH, $character->damageStat());
    }

    public function testStatus(): void
    {
        $character = $this->generateCharacter(1);
        $this->assertFalse($character->hasStatus("abc"));
        $this->assertNull($character->getStatus("abc"));
        $this->assertFalse($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffectDuration::Combat,
            "valueAbsolute" => false,
        ]);
        $this->assertTrue($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        $this->assertFalse($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects[] = new CharacterEffect([
            "id" => "poisonEffect",
            "type" => SkillSpecial::TYPE_POISON,
            "duration" => CharacterEffectDuration::Combat,
            "value" => 5,
            "valueAbsolute" => false,
        ]);
        $this->assertTrue($character->hasStatus(Character::STATUS_POISONED));
        $character->effects->removeByFilter(["id" => "poisonEffect"]);
        $this->assertFalse($character->hasStatus(Character::STATUS_POISONED));
    }

    public function testCanAct(): void
    {
        $character = $this->generateCharacter(1);
        $this->assertTrue($character->canAct());
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffectDuration::Combat,
            "valueAbsolute" => false,
        ]);
        $this->assertFalse($character->canAct());
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        $this->assertTrue($character->canAct());
        $character->harm($character->hitpoints / 2);
        $this->assertTrue($character->canAct());
        $character->harm($character->hitpoints);
        $this->assertFalse($character->canAct());
        $character->heal(1);
        $this->assertTrue($character->canAct());
    }

    public function testCanDefend(): void
    {
        $character = $this->generateCharacter(1);
        $this->assertTrue($character->canDefend());
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffectDuration::Combat,
            "valueAbsolute" => false,
        ]);
        $this->assertFalse($character->canDefend());
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        $this->assertTrue($character->canDefend());
    }
}
