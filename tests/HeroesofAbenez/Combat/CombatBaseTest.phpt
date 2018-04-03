<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

final class CombatBaseTest extends \Tester\TestCase {
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
    $petStats = [
      "id" => $id, "deployed" => true, "bonusStat" => "strength", "bonusValue" => 10
    ];
    $weaponStats = [
      "id" => 1, "name" => "Novice sword", "slot" => Equipment::SLOT_WEAPON, "type" => Equipment::TYPE_SWORD,
      "strength" => 1, "worn" => true,
    ];
    $attackSkillStats = [
      "id" => 1, "name" => "Charge", "target" => SkillAttack::TARGET_SINGLE, "levels" => 5,
      "baseDamage" => "110%", "damageGrowth" => "5%", "strikes" => 1, "hitRate" => NULL,
    ];
    $specialSkillStats = [
      "id" => 1, "name" => "type", "target" => SkillSpecial::TARGET_SELF, "levels" => 5,
      "type" => SkillSpecial::TYPE_BUFF, "stat" => SkillSpecial::STAT_DEFENSE, "value" => 15, "valueGrowth" => 3,
      "duration" => 3,
    ];
    $skills = [
      new CharacterAttackSkill(new SkillAttack($attackSkillStats), 1),
      new CharacterSpecialSkill(new SkillSpecial($specialSkillStats), 1),
    ];
    return new Character($stats, [new Equipment($weaponStats)], [new Pet($petStats)], $skills);
  }
  
  public function testInvalidStates() {
    $combat = new CombatBase(clone $this->logger);
    Assert::exception(function() use($combat) {
      $combat->execute();
    }, InvalidStateException::class);
    $combat->setTeams(new Team(""), new Team(""));
    Assert::exception(function() use($combat) {
      $combat->setTeams(new Team(""), new Team(""));
    }, ImmutableException::class);
  }
  
  public function testEffectProviders() {
    $character1 = $this->generateCharacter(1);
    $character2 = $this->generateCharacter(2);
    $provider = new EffectProvider();
    $character1->addEffectProvider($provider);
    Assert::same(50, $character1->maxHitpointsBase);
    Assert::same(50, $character1->maxHitpoints);
    Assert::same(50, $character1->hitpoints);
    $combat = new CombatBase(clone $this->logger);
    $combat->setDuelParticipants($character1, $character2);
    $combat->onCombatStart($combat);
    Assert::same(50, $character1->maxHitpointsBase);
    Assert::same(60, $character1->maxHitpoints);
    Assert::same(60, $character1->hitpoints);
    $combat->onCombatEnd($combat);
    Assert::same(50, $character1->maxHitpointsBase);
    Assert::same(50, $character1->maxHitpoints);
    Assert::same(50, $character1->hitpoints);
  }
  
  public function testPostCombat() {
    $combat = new CombatBase(clone $this->logger);
    $combat->healers = function(Team $team1, Team $team2): Team {
      $team = new Team("healers");
      foreach(array_merge($team1->items, $team2->items) as $character) {
        $team[] = $character;
      }
      return $team;
    };
    $team1 = new Team("Team 1");
    $team1[] = $this->generateCharacter(1);
    $team2 = new Team("Team 2");
    $team2[] = $this->generateCharacter(2);
    $combat->setTeams($team1, $team2);
    $combat->execute();
    Assert::type("int", $combat->round);
    Assert::true(($combat->round <= 31));
    Assert::type("int", $combat->log->round);
    Assert::same(5000, $combat->log->round);
    $players = array_merge($team1->items, $team2->items);
    /** @var Character $player */
    foreach($players as $player) {
      Assert::same(0, $player->initiative);
    }
  }
}

$test = new CombatBaseTest();
$test->run();
?>