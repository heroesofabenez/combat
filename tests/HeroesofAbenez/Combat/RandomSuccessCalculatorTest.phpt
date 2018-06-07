<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class RandomSuccessCalculatorTest extends \Tester\TestCase {
  /** @var RandomSuccessCalculator */
  protected $calculator;
  
  protected function setUp() {
    $this->calculator = new RandomSuccessCalculator();
  }
  
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testHasHit() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    Assert::type("bool", $this->calculator->hasHit($character1, $character2));
    $skillData = [
      "id" => 1, "name" => "Skill Attack", "baseDamage" => "120%", "damageGrowth" => "2%", "levels" => 5,
      "target" => SkillAttack::TARGET_SINGLE, "strikes" => 1, "hitRate" => "100%",
    ];
    $skill = new SkillAttack($skillData);
    $characterSkill = new CharacterAttackSkill($skill, 1);
    Assert::type("bool", $this->calculator->hasHit($character1, $character2, $characterSkill));
  }
  
  public function testHasHealed() {
    $character1 = $this->generateCharacter(1);
    Assert::type("bool", $this->calculator->hasHealed($character1));
  }
}

$test = new RandomSuccessCalculatorTest();
$test->run();
?>