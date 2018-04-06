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
  
  public function testHasMember() {
    $team = new Team("");
    Assert::false($team->hasMember(1));
    $team[] = $this->generateCharacter(1);
    $team[] = $this->generateCharacter(2);
    Assert::true($team->hasMember(1));
    Assert::false($team->hasMember(3));
  }
  
  public function testHasMembers() {
    $team = new Team("");
    Assert::false($team->hasMembers());
    $team[] = $this->generateCharacter(1);
    $team[] = $this->generateCharacter(2);
    Assert::true($team->hasMembers());
    Assert::true($team->hasMembers(["id" => 1]));
    Assert::false($team->hasMembers(["id" => 3]));
  }
  
  public function testGetMembers() {
    $team = new Team("");
    Assert::count(0, $team->getMembers());
    $team[] = $this->generateCharacter(1);
    $team[] = $this->generateCharacter(2);
    Assert::count(2, $team->getMembers());
    Assert::count(1, $team->getMembers(["id" => 1]));
    Assert::count(0, $team->getMembers(["id" => 3]));
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
}

$test = new TeamTest();
$test->run();
?>