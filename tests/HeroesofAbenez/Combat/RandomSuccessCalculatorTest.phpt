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
  
  public function testCalculateHitChance() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    for($i = 1; $i <= 10; $i++) {
      Assert::notSame(0, $this->calculator->calculateHitChance($character1, $character2));
    }
  }
  
  public function testCalculateHealingSuccessChance() {
    $character1 = $this->generateCharacter(1);
    for($i = 1; $i <= 10; $i++) {
      Assert::notSame(0, $this->calculator->calculateHealingSuccessChance($character1));
    }
  }
  
  public function testHasHit() {
    Assert::type("bool", $this->calculator->hasHit(0));
  }
}

$test = new RandomSuccessCalculatorTest();
$test->run();
?>