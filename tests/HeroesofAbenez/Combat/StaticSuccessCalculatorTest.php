<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use MyTester\Attributes\BeforeTest;
use MyTester\Attributes\Group;
use MyTester\Attributes\TestSuite;

#[TestSuite("StaticSuccessCalculator")]
#[Group("successCalculators")]
final class StaticSuccessCalculatorTest extends \MyTester\TestCase
{
    private StaticSuccessCalculator $calculator;

    #[BeforeTest]
    public function rebuildContainer(): void
    {
        $this->calculator = new StaticSuccessCalculator();
    }

    private function generateCharacter(int $id): Character
    {
        $stats = [
            "id" => $id, "name" => "Player $id", "level" => 1, "initiativeFormula" => "1d2+DEX/4", "strength" => 10,
            "dexterity" => 10, "constitution" => 10, "intelligence" => 10, "charisma" => 10
        ];
        return new Character($stats);
    }

    public function testHasHit(): void
    {
        $character1 = $this->generateCharacter(1);
        $character2 = $this->generateCharacter(2);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertTrue($this->calculator->hasHit($character1, $character2));
        }
    }

    public function testHasHealed(): void
    {
        $character1 = $this->generateCharacter(1);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertTrue($this->calculator->hasHealed($character1));
        }
    }
}
