<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use HeroesofAbenez\Combat\SkillAttack as Skill;
use HeroesofAbenez\Combat\CharacterAttackSkill as CharacterSkill;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("SkillAttack")]
#[Group("combatActions")]
final class SkillAttackTest extends \MyTester\TestCase
{
    use \MyTester\Bridges\NetteDI\TCompiledContainer;

    protected CombatLogger $logger;

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
        $skillData = [
            "id" => 1, "name" => "Skill Attack", "baseDamage" => "60%", "damageGrowth" => "20%", "levels" => 5,
            "target" => Skill::TARGET_SINGLE, "strikes" => 2, "hitRate" => "100%",
        ];
        $skill = new Skill($skillData);
        $characterSkill = new CharacterSkill($skill, 2);
        return new Character($stats, [], [], [$characterSkill]);
    }

    /*public function testShouldUse(): void {
      $character1 = $this->generateCharacter(1);
      $character2 = $this->generateCharacter(2);
      $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
      $combat->setDuelParticipants($character1, $character2);
      $action = new SkillAttack();
      $this->assertFalse($action->shouldUse($combat, $character1));
      for($i = 1; $i <= $character1->skills[0]->skill->cooldown; $i++) {
        $character1->skills[0]->decreaseCooldown();
      }
      $this->assertTrue($action->shouldUse($combat, $character1));
    }*/

    public function testDo(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
        $combat->setDuelParticipants($character1, $character2);
        $combat->onCombatStart($combat);
        $combat->onRoundStart($combat);
        for ($i = 1; $i <= $character1->skills[0]->skill->cooldown; $i++) {
            $character1->skills[0]->decreaseCooldown();
        }
        $action = new SkillAttack();
        $action->do($combat, $character1);
        $this->assertSame(42, $character2->hitpoints);
        $this->assertSame(8, $combat->team1Damage);
        $this->assertCount(1, $combat->log);
        $this->assertCount(2, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        $this->assertType(CombatLogEntry::class, $record);
        $this->assertSame(SkillAttack::ACTION_NAME, $record->action);
        $this->assertSame("Skill Attack", $record->name);
        $this->assertTrue($record->result);
        $this->assertSame(4, $record->amount);
        $this->assertSame($character1->name, $record->character1->name);
        $this->assertSame($character2->name, $record->character2->name);
    }
}
