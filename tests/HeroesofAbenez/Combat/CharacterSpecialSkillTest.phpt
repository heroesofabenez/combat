<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class CharacterSpecialSkillTest extends \Tester\TestCase {
  public function testGetSkillType() {
    $skillData = [
      "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
      "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
    ];
    $skill = new SkillSpecial($skillData);
    $characterSkill = new CharacterSpecialSkill($skill, 1);
    Assert::same("special", $characterSkill->skillType);
  }
  
  public function testGetLevel() {
    $skillData = [
      "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
      "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
    ];
    $skill = new SkillSpecial($skillData);
    $characterSkill = new CharacterSpecialSkill($skill, 1);
    Assert::same(1, $characterSkill->level);
  }
  
  public function testGetValues() {
    $skillData = [
      "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => SkillSpecial::TYPE_BUFF, "duration" => 3,
      "target" => SkillSpecial::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
    ];
    $skill = new SkillSpecial($skillData);
    $characterSkill = new CharacterSpecialSkill($skill, 1);
    Assert::same(10, $characterSkill->value);
    $characterSkill = new CharacterSpecialSkill($skill, 5);
    Assert::same(18, $characterSkill->value);
    $skillData["type"] = SkillSpecial::TYPE_STUN;
    $skill = new SkillSpecial($skillData);
    $characterSkill = new CharacterSpecialSkill($skill, 1);
    Assert::same(0, $characterSkill->value);
  }
}

$test = new CharacterSpecialSkillTest();
$test->run();
?>