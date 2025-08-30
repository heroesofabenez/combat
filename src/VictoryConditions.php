<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * VictoryConditions
 *
 * @author Jakub Konečný
 */
final class VictoryConditions
{
    use \Nette\StaticClass;

    /**
     * Evaluate winner of combat
     * The team which dealt more damage after round limit, wins
     * If all members of one team are eliminated before that, the other team wins
     */
    public static function moreDamage(CombatBase $combat): int
    {
        $result = 0;
        if ($combat->round <= $combat->roundLimit) {
            if (!$combat->team1->hasAliveMembers()) {
                $result = 2;
            } elseif (!$combat->team2->hasAliveMembers()) {
                $result = 1;
            }
        } elseif ($combat->round > $combat->roundLimit) {
            $result = ($combat->team1Damage > $combat->team2Damage) ? 1 : 2;
        }
        return $result;
    }

    /**
     * Evaluate winner of combat
     * Team 1 wins only if they eliminate all opponents before round limit
     */
    public static function eliminateSecondTeam(CombatBase $combat): int
    {
        $result = 0;
        if ($combat->round <= $combat->roundLimit) {
            if (!$combat->team1->hasAliveMembers()) {
                $result = 2;
            } elseif (!$combat->team2->hasAliveMembers()) {
                $result = 1;
            }
        } elseif ($combat->round > $combat->roundLimit) {
            $result = (!$combat->team2->hasAliveMembers()) ? 1 : 2;
        }
        return $result;
    }

    /**
     * Evaluate winner of combat
     * Team 1 wins if at least 1 of its members is alive after round limit
     */
    public static function firstTeamSurvives(CombatBase $combat): int
    {
        $result = 0;
        if ($combat->round <= $combat->roundLimit) {
            if (!$combat->team1->hasAliveMembers()) {
                $result = 2;
            } elseif (!$combat->team2->hasAliveMembers()) {
                $result = 1;
            }
        } elseif ($combat->round > $combat->roundLimit) {
            $result = ($combat->team1->hasAliveMembers()) ? 1 : 2;
        }
        return $result;
    }
}
