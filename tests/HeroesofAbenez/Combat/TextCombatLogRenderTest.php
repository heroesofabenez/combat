<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

require __DIR__ . "/../../bootstrap.php";

use Tester\Assert;
use HeroesofAbenez\Combat\CombatActions;

/**
 * @author Jakub Konečný
 * @testCase
 */
final class TextCombatLogRenderTest extends \Tester\TestCase
{
    use \Testbench\TCompiledContainer;

    protected function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testRendering(): void
    {
        /** @var TextCombatLogRender $render */
        $render = $this->getService(TextCombatLogRender::class);
        /** @var CombatLogger $logger */
        $logger = $this->getService(CombatLogger::class);
        $team1 = new Team("Team 1");
        $team1[] = $character1 = $this->generateCharacter(1);
        $team2 = new Team("Team 2");
        $team2[] = $character2 = $this->generateCharacter(2);
        $logger->setTeams($team1, $team2);
        $logger->round = 1;
        $logger->logText("abc.abc");
        $logger->log([
            "action" => CombatActions\Attack::ACTION_NAME, "name" => "", "result" => true, "amount" => 1,
            "character1" => $character1, "character2" => $character2,
        ]);
        $logger->log([
            "action" => CombatActions\Attack::ACTION_NAME, "name" => "", "result" => false, "amount" => 1,
            "character1" => $character2, "character2" => $character1,
        ]);
        $logger->round = 2;
        $logger->log([
            "action" => CombatActions\SkillAttack::ACTION_NAME, "name" => "Abc", "result" => true, "amount" => 1,
            "character1" => $character1, "character2" => $character2,
        ]);
        $logger->log([
            "action" => CombatActions\SkillAttack::ACTION_NAME, "name" => "Def", "result" => false, "amount" => 1,
            "character1" => $character2, "character2" => $character1,
        ]);
        $logger->log([
            "action" => CombatActions\SkillSpecial::ACTION_NAME, "name" => "Abc", "result" => true, "amount" => 1,
            "character1" => $character1, "character2" => $character2,
        ]);
        $logger->log([
            "action" => CombatActions\SkillSpecial::ACTION_NAME, "name" => "Def", "result" => false, "amount" => 1,
            "character1" => $character2, "character2" => $character1,
        ]);
        $logger->log([
            "action" => CombatActions\Heal::ACTION_NAME, "name" => "", "result" => true, "amount" => 1,
            "character1" => $character1, "character2" => $character2,
        ]);
        $logger->log([
            "action" => CombatActions\Heal::ACTION_NAME, "name" => "", "result" => false, "amount" => 1,
            "character1" => $character2, "character2" => $character1,
        ]);
        $logger->log([
            "action" => CombatLogEntry::ACTION_POISON, "name" => "", "result" => true, "amount" => 1,
            "character1" => $character1, "character2" => $character2,
        ]);
        $params = [
            "team1" => $team1, "team2" => $team2, "actions" => $logger->getIterator(), "title" => "",
        ];
        Assert::same(file_get_contents(__DIR__ . "/CombatLogExpected.latte"), $render->render($params));
    }
}

$test = new TextCombatLogRenderTest();
$test->run();
