<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("CharacterAttackSkill")]
final class CharacterAttackSkillTest extends \MyTester\TestCase
{
    public function testGetSkillType(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
            "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
        ];
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertSame("attack", $characterSkill->skillType);
    }

    public function testGetLevel(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
            "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
        ];
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertSame(1, $characterSkill->level);
    }

    public function testGetDamage(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
            "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
        ];
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertSame(120, $characterSkill->damage);
        $characterSkill = new CharacterAttackSkill($skill, 5);
        $this->assertSame(128, $characterSkill->damage);
    }

    public function testGetHitRate(): void
    {
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
            "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => null,
        ];
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertSame(100, $characterSkill->hitRate);
        $skillData["hitRate"] = "80%";
        $skill = new SkillAttack($skillData);
        $characterSkill = new CharacterAttackSkill($skill, 1);
        $this->assertSame(80, $characterSkill->hitRate);
    }
}
