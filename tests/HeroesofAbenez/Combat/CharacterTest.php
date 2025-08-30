<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class CharacterTest extends \Tester\TestCase
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
        Assert::null($character->activePet);
        $pet->deployed = true;
        Assert::same(1, $character->activePet);
    }

    public function testAddAndRemoveEffect(): void
    {
        $character = $this->generateCharacter(1);
        Assert::count(0, $character->effects);
        Assert::same(5, $character->damage);
        $character->effects[] = new CharacterEffect([
            "id" => "equipment1bonusEffect",
            "type" => "buff",
            "stat" => Character::STAT_DAMAGE,
            "value" => 10,
            "valueAbsolute" => true,
            "duration" => CharacterEffect::DURATION_COMBAT,
        ]);
        Assert::count(1, $character->effects);
        Assert::same(15, $character->damage);
        $character->effects->removeByFilter(["id" => "equipment1bonusEffect"]);
        Assert::count(0, $character->effects);
        Assert::same(5, $character->damage);
    }

    public function testInitiativeFormulaParser(): void
    {
        $character = $this->generateCharacter(1);
        Assert::type(InitiativeFormulaParser::class, $character->initiativeFormulaParser);
        Assert::notEqual(0, $character->initiative);
        $character->initiativeFormulaParser = new ConstantInitiativeFormulaParser(0);
        Assert::equal(0, $character->initiative);
    }

    public function testDebuffsCap(): void
    {
        $character = $this->generateCharacter(1);
        $effect = new CharacterEffect([
            "id" => "skillEffect", "type" => SkillSpecial::TYPE_DEBUFF, "valueAbsolute" => false,
            "value" => 1000, "duration" => 1, "stat" => "constitution",
        ]);
        $character->effects[] = $effect;
        Assert::same(2, $character->constitution);
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
        Assert::same(Character::STAT_STRENGTH, $character->damageStat());
        $equipment[0]->worn = false;
        Assert::same(Character::STAT_INTELLIGENCE, $character->damageStat());
        $equipment[1]->worn = false;
        Assert::same(Character::STAT_STRENGTH, $character->damageStat());
    }

    public function testStatus(): void
    {
        $character = $this->generateCharacter(1);
        Assert::false($character->hasStatus("abc"));
        Assert::null($character->getStatus("abc"));
        Assert::false($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffect::DURATION_COMBAT,
            "valueAbsolute" => false,
        ]);
        Assert::true($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        Assert::false($character->hasStatus(Character::STATUS_STUNNED));
        $character->effects[] = new CharacterEffect([
            "id" => "poisonEffect",
            "type" => SkillSpecial::TYPE_POISON,
            "duration" => CharacterEffect::DURATION_COMBAT,
            "value" => 5,
            "valueAbsolute" => false,
        ]);
        Assert::true($character->hasStatus(Character::STATUS_POISONED));
        $character->effects->removeByFilter(["id" => "poisonEffect"]);
        Assert::false($character->hasStatus(Character::STATUS_POISONED));
    }

    public function testCanAct(): void
    {
        $character = $this->generateCharacter(1);
        Assert::true($character->canAct());
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffect::DURATION_COMBAT,
            "valueAbsolute" => false,
        ]);
        Assert::false($character->canAct());
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        Assert::true($character->canAct());
        $character->harm($character->hitpoints / 2);
        Assert::true($character->canAct());
        $character->harm($character->hitpoints);
        Assert::false($character->canAct());
        $character->heal(1);
        Assert::true($character->canAct());
    }

    public function testCanDefend(): void
    {
        $character = $this->generateCharacter(1);
        Assert::true($character->canDefend());
        $character->effects[] = new CharacterEffect([
            "id" => "stunEffect",
            "type" => SkillSpecial::TYPE_STUN,
            "duration" => CharacterEffect::DURATION_COMBAT,
            "valueAbsolute" => false,
        ]);
        Assert::false($character->canDefend());
        $character->effects->removeByFilter(["id" => "stunEffect"]);
        Assert::true($character->canDefend());
    }
}

$test = new CharacterTest();
$test->run();
