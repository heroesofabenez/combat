<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\BeforeTest;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("RandomSuccessCalculator")]
#[Group("successCalculators")]
final class RandomSuccessCalculatorTest extends \MyTester\TestCase
{
    private RandomSuccessCalculator $calculator;

    #[BeforeTest]
    public function rebuildContainer(): void
    {
        $this->calculator = new RandomSuccessCalculator();
    }

    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testHasHit(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $this->assertType("bool", $this->calculator->hasHit($character1, $character2));
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
            "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
        ];
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertType("bool", $this->calculator->hasHit($character1, $character2, $characterSkill));
        $character2->effects[] = new CharacterEffect([
            "id" => "stunEffect", "type" => SkillSpecial::TYPE_STUN, "valueAbsolute" => false,
            "duration" => CharacterEffectDuration::Combat,
        ]);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertTrue($this->calculator->hasHit($character1, $character2));
            $this->assertTrue($this->calculator->hasHit($character1, $character2, $characterSkill));
        }
    }

    public function testHasHealed(): void
    {
        $character1 = $this->generateCharacter(1);
        $this->assertType("bool", $this->calculator->hasHealed($character1));
    }
}
