<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use HeroesofAbenez\Combat\SkillSpecial as Skill;
use HeroesofAbenez\Combat\CharacterSpecialSkill as CharacterSkill;
use MyTester\Attributes\AfterTest;
use MyTester\Attributes\BeforeTestSuite;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("SkillSpecial")]
#[Group("combatActions")]
final class SkillSpecialTest extends \MyTester\TestCase
{
    use \MyTester\Bridges\NetteDI\TCompiledContainer;

    private CombatLogger $logger;

    public function setUp(): void
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
        $skillData = [
            "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => Skill::TYPE_BUFF, "duration" => 3,
            "target" => Skill::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
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
      $action = new SkillSpecial();
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
        $action = new SkillSpecial();
        $action->do($combat, $character1);
        $this->assertCount(1, $combat->log);
        $this->assertCount(1, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        $this->assertType(CombatLogEntry::class, $record);
        $this->assertSame(SkillSpecial::ACTION_NAME, $record->action);
        $this->assertSame("Skill Special", $record->name);
        $this->assertTrue($record->result);
        $this->assertSame(0, $record->amount);
        $this->assertSame($character1->name, $record->character1->name);
        $this->assertSame($character1->name, $record->character2->name);
        $this->assertCount(1, $character1->effects);
        $effect = $character1->effects[0];
        $this->assertSame(Skill::TYPE_BUFF, $effect->type);
        $this->assertSame(Character::STAT_DAMAGE, $effect->stat);
        $this->assertSame(12, $effect->value);
        $this->assertSame(3, $effect->duration);
    }
}
