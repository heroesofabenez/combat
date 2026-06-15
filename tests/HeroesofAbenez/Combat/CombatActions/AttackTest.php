<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use MyTester\Attributes\AfterTest;
use MyTester\Attributes\BeforeTest;
use MyTester\Attributes\BeforeTestSuite;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("Attack")]
#[Group("combatActions")]
final class AttackTest extends \MyTester\TestCase
{
    use \MyTester\Bridges\NetteDI\TCompiledContainer;

    private CombatLogger $logger;

    #[BeforeTest]
    public function getLogger(): void
    {
        $this->logger = $this->getService(CombatLogger::class);
    }

    #[AfterTest]
    #[BeforeTestSuite]
    public function rebuildContainer(): void
    {
        $this->refreshContainer();
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
        $action = new Attack();
        $this->assertTrue($action->shouldUse($combat, $character1));
        $this->assertTrue($action->shouldUse($combat, $character2));
    }

    public function testDo(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
        $combat->setDuelParticipants($character1, $character2);
        $combat->onCombatStart($combat);
        $combat->onRoundStart($combat);
        $action = new Attack();
        $action->do($combat, $character1);
        $this->assertSame(45, $character2->hitpoints);
        $this->assertSame(5, $combat->team1Damage);
        $this->assertCount(1, $combat->log);
        $this->assertCount(1, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        $this->assertType(CombatLogEntry::class, $record);
        $this->assertSame(Attack::ACTION_NAME, $record->action);
        $this->assertSame("", $record->name);
        $this->assertTrue($record->result);
        $this->assertSame(5, $record->amount);
        $this->assertSame($character1->name, $record->character1->name);
        $this->assertSame($character2->name, $record->character2->name);
    }
}
