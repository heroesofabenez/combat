<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class PetTest extends \Tester\TestCase {
  public function testGetCombatEffects() {
    $pet = new Pet([
      "id" => 1, "deployed" => false, "bonusStat" => Character::STAT_STRENGTH, "bonusValue" => 10,
    ]);
    Assert::count(0, $pet->getCombatEffects());
    $pet->deployed = true;
    Assert::count(1, $pet->getCombatEffects());
  }
}

$test = new PetTest();
$test->run();
?>