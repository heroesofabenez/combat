<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class PetTest extends \Tester\TestCase {
  public function testToCombatEffect() {
    $petStats = [
      "id" =>1, "deployed" => false, "bonusStat" => Pet::STAT_STRENGTH, "bonusValue" => 10,
    ];
    Assert::null((new Pet($petStats))->toCombatEffect());
    $petStats["deployed"] = true;
    Assert::type(CharacterEffect::class, (new Pet($petStats))->toCombatEffect());
  }
}

$test = new PetTest();
$test->run();
?>