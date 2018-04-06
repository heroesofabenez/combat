<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class PetTest extends \Tester\TestCase {
  public function testToCombatEffect() {
    $pet = new Pet([
      "id" =>1, "deployed" => false, "bonusStat" => Pet::STAT_STRENGTH, "bonusValue" => 10,
    ]);
    Assert::null($pet->toCombatEffect());
    $pet->deployed = true;
    Assert::type(CharacterEffect::class, $pet->toCombatEffect());
  }
}

$test = new PetTest();
$test->run();
?>