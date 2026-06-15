<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("CombatLogger")]
final class CombatLoggerTest extends \MyTester\TestCase
{
    use \MyTester\Bridges\NetteDI\TCompiledContainer;

    public function setUp(): void
    {
        $this->refreshContainer();
    }

    public function testInvalidStates(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $logger->setTeams(new Team("Team1"), new Team("Team 2"));
        $this->assertThrowsException(static function () use ($logger) {
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
        $this->assertSame($title, $logger->title);
        $log = (string) $logger;
        $this->assertContains("<title>$title Combat</title>", $log);
    }

    public function testCount(): void
    {
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $this->assertCount(0, $logger);
        $logger->logText("abc");
        $this->assertCount(1, $logger);
    }

    private function generateCharacter(int $id): Character
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
        $this->assertType("string", (string) $logger);
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
            $this->assertSame(1, $round);
            $this->assertType("array", $actions);
            $this->assertCount(5, $actions);
        }
    }
}
