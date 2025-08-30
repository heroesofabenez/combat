<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class CombatLoggerTest extends \Tester\TestCase
{
    use \Testbench\TCompiledContainer;

    protected function setUp()
    {
        $this->refreshContainer();
    }

    public function testInvalidStates(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $logger->setTeams(new Team("Team1"), new Team("Team 2"));
        Assert::exception(function () use ($logger) {
            $logger->setTeams(new Team("Team1"), new Team("Team 2"));
        }, ImmutableException::class);
    }

    public function testTitle(): void
    {
        $title = "ABC";
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $logger->setTeams(new Team("Team1"), new Team("Team 2"));
        $logger->title = $title;
        Assert::same($title, $logger->title);
        $log = (string) $logger;
        Assert::contains("<title>$title Combat</title>", $log);
    }

    public function testCount(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        Assert::count(0, $logger);
        $logger->logText("abc");
        Assert::count(1, $logger);
    }

    protected function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testRendering(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $team1 = new Team("Team 1");
        $team1[] = $this->generateCharacter(1);
        $team2 = new Team("Team 2");
        $team2[] = $this->generateCharacter(2);
        $logger->setTeams($team1, $team2);
        $logger->round = 1;
        $logger->logText("abc.abc");
        $logger->logText("abc.abc");
        $logger->round = 2;
        $logger->logText("abc.abc");
        $logger->logText("abc.abc");
        Assert::type("string", (string) $logger);
    }

    public function testGetIterator(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $logger->round = 1;
        for ($i = 1; $i <= 5; $i++) {
            $logger->logText("abc");
        }
        foreach ($logger as $round => $actions) {
            Assert::same(1, $round);
            Assert::type("array", $actions);
            Assert::count(5, $actions);
        }
    }
}

$test = new CombatLoggerTest();
$test->run();
