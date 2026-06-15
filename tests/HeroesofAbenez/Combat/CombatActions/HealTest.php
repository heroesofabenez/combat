<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\Team;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("Heal")]
#[Group("combatActions")]
final class HealTest extends \MyTester\TestCase
{
    use \MyTester\Bridges\NetteDI\TCompiledContainer;

    private CombatLogger $logger;

    public function setUp(): void
    {
        $this->logger = $this->getService(CombatLogger::class);
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
        $this->assertFalse($action->shouldUse($combat, $character1));
        $character1->harm(30);
        $this->assertTrue($action->shouldUse($combat, $character1));
        $character1->harm(20);
        $this->assertFalse($action->shouldUse($combat, $character1));
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
        $this->assertSame(20, $character1->hitpoints);
        $action->do($combat, $character1);
        $this->assertSame(25, $character1->hitpoints);
        $this->assertCount(1, $combat->log);
        $this->assertCount(1, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        $this->assertType(CombatLogEntry::class, $record);
        $this->assertSame(Heal::ACTION_NAME, $record->action);
        $this->assertSame("", $record->name);
        $this->assertTrue($record->result);
        $this->assertSame(5, $record->amount);
        $this->assertSame($character1->name, $record->character1->name);
        $this->assertSame($character1->name, $record->character2->name);
    }
}
