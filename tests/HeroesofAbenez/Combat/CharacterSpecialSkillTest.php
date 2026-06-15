<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("CharacterSpecialSkill")]
final class CharacterSpecialSkillTest extends \MyTester\TestCase
{
    public function testGetSkillType(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
            "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
        ];
        $skill = new SkillSpecial($skillData);
        $characterSkill = new CharacterSpecialSkill($skill, 1);
        $this->assertSame("special", $characterSkill->skillType);
    }

    public function testGetLevel(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
            "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
        ];
        $skill = new SkillSpecial($skillData);
        $characterSkill = new CharacterSpecialSkill($skill, 1);
        $this->assertSame(1, $characterSkill->level);
    }

    public function testGetValues(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
            "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
        ];
        $skill = new SkillSpecial($skillData);
        $characterSkill = new CharacterSpecialSkill($skill, 1);
        $this->assertSame(10, $characterSkill->value);
        $characterSkill = new CharacterSpecialSkill($skill, 5);
        $this->assertSame(18, $characterSkill->value);
        $skillData["type"] = SkillSpecial::TYPE_STUN;
        $skill = new SkillSpecial($skillData);
        $characterSkill = new CharacterSpecialSkill($skill, 1);
        $this->assertSame(0, $characterSkill->value);
    }
}
