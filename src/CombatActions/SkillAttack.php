<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CharacterAttackSkill;
use HeroesofAbenez\Combat\ICombatAction;
use HeroesofAbenez\Combat\SkillAttack as Skill;
use HeroesofAbenez\Combat\NotImplementedException;
use Nexendrie\Utils\Numbers;

final class SkillAttack implements ICombatAction
{
    public const ACTION_NAME = "skill_attack";

    public function getName(): string
    {
        return self::ACTION_NAME;
    }

    public function getPriority(): int
    {
        return 1001;
    }

    public function shouldUse(CombatBase $combat, Character $character): bool
    {
        $attackTarget = $combat->selectAttackTarget($character);
        if ($attackTarget === null) {
            return false;
        }
        if (count($character->usableSkills) < 1) {
            return false;
        }
        return ($character->usableSkills[0] instanceof CharacterAttackSkill);
    }

    private function doSingleAttack(
        Character $attacker,
        Character $defender,
        CharacterAttackSkill $skill,
        CombatBase $combat
    ): void {
        $result = [];
        $result["result"] = $combat->successCalculator->hasHit($attacker, $defender, $skill);
        $result["amount"] = 0;
        if ($result["result"]) {
            $amount = (int) (($attacker->damage - $defender->defense) / 100 * $skill->damage);
            $result["amount"] = Numbers::clamp($amount, 0, $defender->hitpoints);
        }
        if ($result["amount"] > 0) {
            $defender->harm($result["amount"]);
        }
        $result["action"] = $this->getName();
        $result["name"] = $skill->skill->name;
        $result["character1"] = $attacker;
        $result["character2"] = $defender;
        $combat->logDamage($attacker, $result["amount"]);
        $combat->log->log($result);
        $skill->resetCooldown();
    }

    /**
     * @throws NotImplementedException
     */
    public function do(CombatBase $combat, Character $character): void
    {
        /** @var CharacterAttackSkill $skill */
        $skill = $character->usableSkills[0];
        /** @var Character $primaryTarget */
        $primaryTarget = $combat->selectAttackTarget($character);
        $targets = match ($skill->skill->target) {
            Skill::TARGET_SINGLE => [$primaryTarget],
            Skill::TARGET_ROW => $combat->getTeam($primaryTarget)->getItems(["positionRow" => $primaryTarget->positionRow]),
            Skill::TARGET_COLUMN => $combat->getTeam($primaryTarget)->getItems(["positionColumn" => $primaryTarget->positionColumn]),
            default => throw new NotImplementedException("Target {$skill->skill->target} for attack skills is not implemented."),
        };
        foreach ($targets as $target) {
            for ($i = 1; $i <= $skill->skill->strikes; $i++) {
                if ($target->hitpoints > 0) {
                    $this->doSingleAttack($character, $target, $skill, $combat);
                }
            }
        }
    }
}
