<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\TestSuite;

#[TestSuite("DefaultInitiativeFormulaParser")]
final class DefaultInitiativeFormulaParserTest extends \MyTester\TestCase
{
    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d4+DEX/4", "strength" => 10,
            "dexterity" => 12, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testCalculateInitiative(): void
    {
        $character = $this->generateCharacter(1);
        $parser = new DefaultInitiativeFormulaParser();
        for ($i = 1; $i <= 10; $i++) {
            $initiative = $parser->calculateInitiative($character);
            $this->assertTrue($initiative >= 4);
            $this->assertTrue($initiative <= 8);
        }
    }
}
