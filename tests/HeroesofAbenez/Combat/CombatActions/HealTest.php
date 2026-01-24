<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use Tester\Assert;
use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\Team;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @author Jakub Konečný
 * @testCase
 */
final class HealTest extends \Tester\TestCase
{
    use \Testbench\TCompiledContainer;

    private CombatLogger $logger;

    public function setUp(): void
    {
        $this->logger = $this->getService(CombatLogger::class); // @phpstan-ignore assign.propertyType
    }

    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testShouldUse(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
        $combat->setDuelParticipants($character1, $character2);
        $combat->healers = static fn(Team $team1, Team $team2) => Team::fromArray($team1->toArray(), "healers");
        $action = new Heal();
        Assert::false($action->shouldUse($combat, $character1));
        $character1->harm(30);
        Assert::true($action->shouldUse($combat, $character1));
        $character1->harm(20);
        Assert::false($action->shouldUse($combat, $character1));
    }

    public function testDo(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
        $combat->setDuelParticipants($character1, $character2);
        $combat->healers = static fn(Team $team1, Team $team2) => Team::fromArray($team1->toArray(), "healers");
        $combat->onCombatStart($combat);
        $combat->onRoundStart($combat);
        $action = new Heal();
        $character1->harm(30);
        Assert::same(20, $character1->hitpoints);
        $action->do($combat, $character1);
        Assert::same(25, $character1->hitpoints);
        Assert::count(1, $combat->log);
        Assert::count(1, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        Assert::type(CombatLogEntry::class, $record);
        Assert::same(Heal::ACTION_NAME, $record->action);
        Assert::same("", $record->name);
        Assert::true($record->result);
        Assert::same(5, $record->amount);
        Assert::same($character1->name, $record->character1->name);
        Assert::same($character1->name, $record->character2->name);
    }
}

$test = new HealTest();
$test->run();
