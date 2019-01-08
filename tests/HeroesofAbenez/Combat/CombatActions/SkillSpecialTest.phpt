<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use Tester\Assert;
use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogger;
use HeroesofAbenez\Combat\StaticSuccessCalculator;
use HeroesofAbenez\Combat\CombatLogEntry;
use HeroesofAbenez\Combat\SkillSpecial as Skill;
use HeroesofAbenez\Combat\CharacterSpecialSkill as CharacterSkill;

require __DIR__ . "/../../../bootstrap.php";

final class SkillSpecialTest extends \Tester\TestCase {
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
    $skillData = [
      "id" => 1, "name" => "Skill Special", "levels" => 5, "type" => Skill::TYPE_BUFF, "duration" => 3,
      "target" => Skill::TARGET_SELF, "stat" => Character::STAT_DAMAGE, "value" => 10, "valueGrowth" => 2,
    ];
    $skill = new Skill($skillData);
    $characterSkill = new CharacterSkill($skill, 2);
    return new Character($stats, [], [], [$characterSkill]);
  }

  /*public function testShouldUse() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
    $combat->setDuelParticipants($character1, $character2);
    $action = new SkillSpecial();
    Assert::false($action->shouldUse($combat, $character1));
    for($i = 1; $i <= $character1->skills[0]->skill->cooldown; $i++) {
      $character1->skills[0]->decreaseCooldown();
    }
    Assert::true($action->shouldUse($combat, $character1));
  }*/

  public function testDo() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    $combat = new CombatBase(clone $this->logger, new StaticSuccessCalculator());
    $combat->setDuelParticipants($character1, $character2);
    $combat->onCombatStart($combat);
    $combat->onRoundStart($combat);
    for($i = 1; $i <= $character1->skills[0]->skill->cooldown; $i++) {
      $character1->skills[0]->decreaseCooldown();
    }
    $action = new SkillSpecial();
    $action->do($combat, $character1);
    Assert::count(1, $combat->log);
    Assert::count(1, $combat->log->getIterator()[1]);
    /** @var CombatLogEntry $record */
    $record = $combat->log->getIterator()[1][0];
    Assert::type(CombatLogEntry::class, $record);
    Assert::same(SkillSpecial::ACTION_NAME, $record->action);
    Assert::same("Skill Special", $record->name);
    Assert::true($record->result);
    Assert::same(0, $record->amount);
    Assert::same($character1->name, $record->character1->name);
    Assert::same($character1->name, $record->character2->name);
    Assert::count(1, $character1->effects);
    $effect = $character1->effects[0];
    Assert::same(Skill::TYPE_BUFF, $effect->type);
    Assert::same(Character::STAT_DAMAGE, $effect->stat);
    Assert::same(12, $effect->value);
    Assert::same(3, $effect->duration);
  }
}

$test = new SkillSpecialTest();
$test->run();
?>