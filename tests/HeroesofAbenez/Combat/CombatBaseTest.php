<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @author Jakub Konečný
 * @testCase
 */
final class CombatBaseTest extends \Tester\TestCase
{
    use \Testbench\TCompiledContainer;

    protected CombatLogger $logger;

    public function setUp()
    {
        $this->logger = $this->getService(CombatLogger::class); // @phpstan-ignore assign.propertyType
    }

    protected function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        $petStats = [
            "id" => $id, "deployed" => true, "bonusStat" => "strength", "bonusValue" => 10
        ];
        $weaponStats = [
            "id" => 1, "name" => "Novice sword", "slot" => Equipment::SLOT_WEAPON, "type" => Weapon::TYPE_SWORD,
            "strength" => 1, "worn" => true,
        ];
        $attackSkillStats = [
            "id" => 1, "name" => "Charge", "target" => SkillAttack::TARGET_SINGLE, "levels" => 5,
            "baseDamage" => "110%", "damageGrowth" => "5%", "strikes" => 1, "hitRate" => null,
        ];
        $specialSkillStats = [
            "id" => 1, "name" => "type", "target" => SkillSpecial::TARGET_SELF, "levels" => 5,
            "type" => SkillSpecial::TYPE_BUFF, "stat" => Character::STAT_DEFENSE, "value" => 15, "valueGrowth" => 3,
            "duration" => 3,
        ];
        $skills = [
            new CharacterAttackSkill(new SkillAttack($attackSkillStats), 1),
            new CharacterSpecialSkill(new SkillSpecial($specialSkillStats), 1),
        ];
        return new Character($stats, [new Weapon($weaponStats)], [new Pet($petStats)], $skills);
    }

    public function testInvalidStates(): void
    {
        $combat = new CombatBase(clone $this->logger);
        Assert::exception(function () use ($combat) {
            $combat->execute();
        }, InvalidStateException::class);
        $combat->setTeams(new Team(""), new Team(""));
        Assert::exception(function () use ($combat) {
            $combat->setTeams(new Team(""), new Team(""));
        }, ImmutableException::class);
    }

    public function testVictoryConditions(): void
    {
        $combat = new CombatBase(clone $this->logger);
        Assert::same([VictoryConditions::class, "moreDamage"], $combat->victoryCondition);
        $combat->victoryCondition = [VictoryConditions::class, "eliminateSecondTeam"];
        Assert::same([VictoryConditions::class, "eliminateSecondTeam"], $combat->victoryCondition);
    }

    public function testEffectProviders(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $provider = new EffectsProvider();
        $character1->effectProviders[] = $provider;
        Assert::same(50, $character1->maxHitpointsBase);
        Assert::same(50, $character1->maxHitpoints);
        Assert::same(50, $character1->hitpoints);
        $combat = new CombatBase(clone $this->logger);
        $combat->setDuelParticipants($character1, $character2);
        $combat->onRoundStart($combat);
        Assert::same(50, $character1->maxHitpointsBase);
        Assert::same(60, $character1->maxHitpoints);
        Assert::same(60, $character1->hitpoints);
        $provider->value = 1;
        $combat->onRoundStart($combat);
        Assert::same(50, $character1->maxHitpointsBase);
        Assert::same(51, $character1->maxHitpoints);
        Assert::same(51, $character1->hitpoints);
        $combat->onCombatEnd($combat);
        Assert::same(50, $character1->maxHitpointsBase);
        Assert::same(50, $character1->maxHitpoints);
        Assert::same(50, $character1->hitpoints);
    }

    public function testSuccessCalculator(): void
    {
        $combat = new CombatBase(clone $this->logger);
        Assert::type(RandomSuccessCalculator::class, $combat->successCalculator);
        $combat->successCalculator = new StaticSuccessCalculator();
        Assert::type(StaticSuccessCalculator::class, $combat->successCalculator);
    }

    public function testActionSelector(): void
    {
        $combat = new CombatBase(clone $this->logger);
        Assert::type(CombatActionSelector::class, $combat->actionSelector);
        $combat->actionSelector = new ActionSelector();
        Assert::type(ActionSelector::class, $combat->actionSelector);
    }

    public function testAssignPositions(): void
    {
        $combat = new CombatBase(clone $this->logger);
        $team1 = new Team("");
        $team1->maxRowSize = 2;
        $team1[] = $this->generateCharacter(1);
        $team1[0]->positionRow = $team1[0]->positionColumn = 1;
        $team1[] = $this->generateCharacter(2);
        $team1[] = $this->generateCharacter(3);
        $team1[] = $this->generateCharacter(4);
        $team2 = new Team("");
        $team2->maxRowSize = 2;
        $team2[] = $this->generateCharacter(5);
        $team2[] = $this->generateCharacter(6);
        $team2[] = $this->generateCharacter(7);
        $team2[] = $this->generateCharacter(8);
        $combat->setTeams($team1, $team2);
        $combat->assignPositions($combat);
        Assert::count(2, $team1->getItems(["positionRow" => 1]));
        Assert::count(2, $team1->getItems(["positionRow" => 2]));
        Assert::count(0, $team1->getItems(["positionRow" => 3]));
        Assert::count(2, $team1->getItems(["positionColumn" => 1]));
        Assert::count(2, $team1->getItems(["positionColumn" => 2]));
        Assert::count(0, $team1->getItems(["positionColumn" => 3]));
        Assert::count(2, $team2->getItems(["positionRow" => 1]));
        Assert::count(2, $team2->getItems(["positionRow" => 2]));
        Assert::count(0, $team2->getItems(["positionRow" => 3]));
        Assert::count(2, $team2->getItems(["positionColumn" => 1]));
        Assert::count(2, $team2->getItems(["positionColumn" => 2]));
        Assert::count(0, $team2->getItems(["positionColumn" => 3]));
    }

    public function testDecreaseEffectsDuration(): void
    {
        $combat = new CombatBase(clone $this->logger);
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat->setDuelParticipants($character1, $character2);
        Assert::count(0, $character1->effects);
        $effect = new CharacterEffect([
            "id" => "skillEffect", "type" => SkillSpecial::TYPE_STUN, "valueAbsolute" => false,
            "value" => 0, "duration" => 1, "stat" => "",
        ]);
        $character1->effects[] = $effect;
        Assert::count(1, $character1->effects);
        Assert::true($character1->hasStatus(Character::STATUS_STUNNED));
        $combat->decreaseEffectsDuration($combat);
        Assert::same(0, $effect->duration);
        $character1->recalculateStats();
        Assert::count(0, $character1->effects);
        Assert::false($character1->hasStatus(Character::STATUS_STUNNED));
    }

    public function testApplyPoison(): void
    {
        $combat = new CombatBase(clone $this->logger);
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat->setDuelParticipants($character1, $character2);
        $effect = new CharacterEffect([
            "id" => "skillEffect", "type" => SkillSpecial::TYPE_POISON, "valueAbsolute" => false,
            "value" => 5, "duration" => 1, "stat" => "",
        ]);
        $character1->effects[] = $effect;
        $character1->effects[] = $effect;
        Assert::same(50, $character1->hitpoints);
        $combat->applyPoison($combat);
        Assert::same(40, $character1->hitpoints);
    }

    public function testPostCombat(): void
    {
        $combat = new CombatBase(clone $this->logger);
        $combat->healers = function (Team $team1, Team $team2): Team {
            return Team::fromArray(array_merge($team1->toArray(), $team2->toArray()), "healers");
        };
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        $combat->setDuelParticipants($character1, $character2);
        $combat->execute();
        Assert::same(31, $combat->round);
        Assert::same(5000, $combat->log->round);
        Assert::count(1, $combat->team1->getItems(["initiative" => 0]));
        Assert::count(1, $combat->team2->getItems(["initiative" => 0]));
    }
}

$test = new CombatBaseTest();
$test->run();
