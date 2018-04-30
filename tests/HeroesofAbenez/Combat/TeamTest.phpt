<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;

final class TeamTest extends \Tester\TestCase {
  protected function generateCharacter(int $id): Character {
    $stats = [
      "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
      "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
    ];
    return new Character($stats);
  }
  
  public function testGetName() {
    $name = "Team 1";
    $team = new Team($name);
    Assert::same($name, $team->name);
  }
  
  public function testMaxRowSize() {
    $team = new Team("");
    $team->maxRowSize = 1;
    Assert::same(1, $team->maxRowSize);
  }
  
  public function testGetAliveMembers() {
    $team = new Team("");
    Assert::count(0, $team->aliveMembers);
    $team[] = $this->generateCharacter(1);
    Assert::count(1, $team->aliveMembers);
    $team[] = $this->generateCharacter(2);
    Assert::count(2, $team->aliveMembers);
    $team[0]->harm($team[0]->maxHitpoints);
    Assert::count(1, $team->aliveMembers);
  }
  
  public function testGetUsableMembers() {
    $team = new Team("");
    Assert::count(0, $team->usableMembers);
    $team[] = $this->generateCharacter(1);
    Assert::count(1, $team->usableMembers);
    $team[] = $this->generateCharacter(2);
    Assert::count(2, $team->usableMembers);
    $team[0]->harm($team[0]->maxHitpoints);
    Assert::count(1, $team->usableMembers);
    $stunEffect = [
      "id" => "stunEffect", "type" => SkillSpecial::TYPE_STUN, "source" => CharacterEffect::SOURCE_SKILL,
      "value" => 0, "duration" => CharacterEffect::DURATION_FOREVER, "stat" => "",
    ];
    $team[1]->addEffect(new CharacterEffect($stunEffect));
    $team[1]->recalculateStats();
    Assert::count(0, $team->usableMembers);
  }
  
  public function testHasAliveMembers() {
    $team = new Team("");
    Assert::false($team->hasAliveMembers());
    $team[] = $this->generateCharacter(1);
    Assert::true($team->hasAliveMembers());
    $team[0]->harm($team[0]->maxHitpoints);
    Assert::false($team->hasAliveMembers());
  }
  
  public function testSetCharacterPosition() {
    $team = new Team("");
    $team[] = $this->generateCharacter(1);
    $team->setCharacterPosition(1, 1, 1);
    Assert::same(1, $team[0]->positionRow);
    Assert::same(1, $team[0]->positionColumn);
    Assert::exception(function() use($team) {
      $team->setCharacterPosition(2, 1, 1);
    }, \OutOfBoundsException::class);
    $team[] = $this->generateCharacter(2);
    Assert::exception(function() use($team) {
      $team->setCharacterPosition(2, 1, 1);
    }, InvalidCharacterPositionException::class, NULL, InvalidCharacterPositionException::POSITION_OCCUPIED);
    Assert::exception(function() use($team) {
      $team->maxRowSize = 1;
      $team->setCharacterPosition(2, 1, 2);
    }, InvalidCharacterPositionException::class, NULL, InvalidCharacterPositionException::ROW_FULL);
  }
  
  public function testGetRandomCharacter() {
    $team = new Team("");
    Assert::null($team->getRandomCharacter());
    $team[] = $this->generateCharacter(1);
    Assert::same($team[0], $team->getRandomCharacter());
    $team[] = $this->generateCharacter(2);
    Assert::type(Character::class, $team->getRandomCharacter());
  }
  
  public function testGetLowestHpCharacter() {
    $team = new Team("");
    Assert::null($team->getLowestHpCharacter());
    $team[] = $this->generateCharacter(1);
    Assert::null($team->getLowestHpCharacter());
    $team[0]->harm(10);
    Assert::same($team[0], $team->getLowestHpCharacter(0.8));
    Assert::null($team->getLowestHpCharacter());
    $team[0]->harm(20);
    Assert::same($team[0], $team->getLowestHpCharacter());
  }
  
  public function testGetRowToAttack() {
    $team = new Team("");
    Assert::null($team->getRowToAttack());
    $team[] = $character1 = $this->generateCharacter(1);
    $character1->positionRow = 1;
    $team[] = $character2 = $this->generateCharacter(2);
    $character2->positionRow = 2;
    Assert::same(1, $team->getRowToAttack());
    $character1->harm($character1->maxHitpoints);
    Assert::same(2, $team->getRowToAttack());
    $character2->harm($character2->maxHitpoints);
    Assert::null($team->getRowToAttack());
  }
}

$test = new TeamTest();
$test->run();
?>