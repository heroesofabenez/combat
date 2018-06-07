<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class StaticSuccessCalculatorTest extends \Tester\TestCase {
  /** @var StaticSuccessCalculator */
  protected $calculator;
  
  protected function setUp() {
    $this->calculator = new StaticSuccessCalculator();
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
    for($i = 1; $i <= 10; $i++) {
      Assert::true($this->calculator->hasHit($character1, $character2));
    }
  }
  
  public function testHasHealed() {
    $character1 = $this->generateCharacter(1);
    for($i = 1; $i <= 10; $i++) {
      Assert::true($this->calculator->hasHealed($character1));
    }
  }
}

$test = new StaticSuccessCalculatorTest();
$test->run();
?>