<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use Tester\Assert;
use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;

require __DIR__ . "/../../../bootstrap.php";

final class AttackTest extends \Tester\TestCase {
  protected CombatLogger $logger;

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

  public function testShouldUse() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
    $combat->setDuelParticipants($character1, $character2);
    $action = new Attack();
    Assert::true($action->shouldUse($combat, $character1));
    Assert::true($action->shouldUse($combat, $character2));
  }

  public function testDo() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
    $combat->setDuelParticipants($character1, $character2);
    $combat->onCombatStart($combat);
    $combat->onRoundStart($combat);
    $action = new Attack();
    $action->do($combat, $character1);
    Assert::same(45, $character2->hitpoints);
    Assert::same(5, $combat->team1Damage);
    Assert::count(1, $combat->log);
    Assert::count(1, $combat->log->getIterator()[1]);
    /** @var CombatLogEntry $record */
    $record = $combat->log->getIterator()[1][0];
    Assert::type(CombatLogEntry::class, $record);
    Assert::same(Attack::ACTION_NAME, $record->action);
    Assert::same("", $record->name);
    Assert::true($record->result);
    Assert::same(5, $record->amount);
    Assert::same($character1->name, $record->character1->name);
    Assert::same($character2->name, $record->character2->name);
  }
}

$test = new AttackTest();
$test->run();
?>