<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class CharacterAttackSkillTest extends \Tester\TestCase {
  public function testGetSkillType() {
    $skillData = [
      "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
      "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
    ];
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::same("attack", $characterSkill->skillType);
  }
  
  public function testGetLevel() {
    $skillData = [
      "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
      "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
    ];
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::same(1, $characterSkill->level);
  }
  
  public function testGetDamage() {
    $skillData = [
      "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
      "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
    ];
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::same(120, $characterSkill->damage);
    $characterSkill = new CharacterAttackSkill($skill, 5);
    Assert::same(128, $characterSkill->damage);
  }
  
  public function testGetHitRate() {
    $skillData = [
      "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
      "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => NULL,
    ];
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::same(100, $characterSkill->hitRate);
    $skillData["hitRate"] = "80%";
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::same(80, $characterSkill->hitRate);
  }
}

$test = new CharacterAttackSkillTest();
$test->run();
?>