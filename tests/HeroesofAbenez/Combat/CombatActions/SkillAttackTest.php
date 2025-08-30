<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use Tester\Assert;
use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use HeroesofAbenez\Combat\SkillAttack as Skill;
use HeroesofAbenez\Combat\CharacterAttackSkill as CharacterSkill;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @author Jakub Konečný
 * @testCase
 */
final class SkillAttackTest extends \Tester\TestCase
{
    use \Testbench\TCompiledContainer;

    protected CombatLogger $logger;

    public function setUp(): void
    {
        $this->logger = $this->getService(CombatLogger::class); // @phpstan-ignore assign.propertyType
    }

    protected function generateCharacter(int $id): Character
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
      Assert::false($action->shouldUse($combat, $character1));
      for($i = 1; $i <= $character1->skills[0]->skill->cooldown; $i++) {
        $character1->skills[0]->decreaseCooldown();
      }
      Assert::true($action->shouldUse($combat, $character1));
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
        Assert::same(42, $character2->hitpoints);
        Assert::same(8, $combat->team1Damage);
        Assert::count(1, $combat->log);
        Assert::count(2, $combat->log->getIterator()[1]);
        /** @var CombatLogEntry $record */
        $record = $combat->log->getIterator()[1][0];
        Assert::type(CombatLogEntry::class, $record);
        Assert::same(SkillAttack::ACTION_NAME, $record->action);
        Assert::same("Skill Attack", $record->name);
        Assert::true($record->result);
        Assert::same(4, $record->amount);
        Assert::same($character1->name, $record->character1->name);
        Assert::same($character2->name, $record->character2->name);
    }
}

$test = new SkillAttackTest();
$test->run();
