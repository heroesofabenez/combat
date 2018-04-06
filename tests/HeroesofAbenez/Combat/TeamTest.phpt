<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class TeamTest extends \Tester\TestCase {
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testHasMembers() {
    $team = new Team("");
    Assert::false($team->hasMembers());
    $team[] = $this->generateCharacter(1);
    $team[] = $this->generateCharacter(2);
    Assert::true($team->hasMembers());
    Assert::true($team->hasMembers(["id" => 1]));
    Assert::false($team->hasMembers(["id" => 3]));
  }
  
  public function testGetMembers() {
    $team = new Team("");
    Assert::count(0, $team->getMembers());
    $team[] = $this->generateCharacter(1);
    $team[] = $this->generateCharacter(2);
    Assert::count(2, $team->getMembers());
    Assert::count(1, $team->getMembers(["id" => 1]));
    Assert::count(0, $team->getMembers(["id" => 3]));
  }
}

$test = new TeamTest();
$test->run();
?>