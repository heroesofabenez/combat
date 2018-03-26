<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

final class CombatBaseTest extends \Tester\TestCase {
  /** @var CombatLogger */
  protected $logger;
  
  use \Testbench\TCompiledContainer;
  
  public function setUp() {
    $this->logger = $this->getService(CombatLogger::class);
  }
  
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testInvalidStates() {
    $combat = new CombatBase(clone $this->logger);
    Assert::exception(function() use($combat) {
      $combat->execute();
    }, InvalidStateException::class);
    $combat->setTeams(new Team(""), new Team(""));
    Assert::exception(function() use($combat) {
      $combat->setTeams(new Team(""), new Team(""));
    }, ImmutableException::class);
  }
  
  public function testPostCombat() {
    $combat = new CombatBase(clone $this->logger);
    $team1 = new Team("Team 1");
    $team1[] = $this->generateCharacter(1);
    $team2 = new Team("Team 2");
    $team2[] = $this->generateCharacter(2);
    $combat->setTeams($team1, $team2);
    $combat->execute();
    Assert::type("int", $combat->round);
    Assert::true(($combat->round <= 31));
    Assert::type("int", $combat->log->round);
    Assert::same(5000, $combat->log->round);
    $players = array_merge($team1->items, $team2->items);
    /** @var Character $player */
    foreach($players as $player) {
      Assert::same(0, $player->initiative);
    }
  }
}

$test = new CombatBaseTest();
$test->run();
?>