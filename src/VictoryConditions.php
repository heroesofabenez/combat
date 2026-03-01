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
    private function __construct()
    {
    }

    /**
     * Evaluate winner of combat
     * The team which dealt more damage after round limit, wins
     * If all members of one team are eliminated before that, the other team wins
     */
    public static function moreDamage(CombatBase $combat): int
    {
        if ($combat->round > $combat->roundLimit) {
            return $combat->team1Damage > $combat->team2Damage ? 1 : 2;
        } elseif (!$combat->team1->hasAliveMembers()) {
            return 2;
        } elseif (!$combat->team2->hasAliveMembers()) {
            return 1;
        }
        return 0;
    }

    /**
     * Evaluate winner of combat
     * Team 1 wins only if they eliminate all opponents before round limit
     */
    public static function eliminateSecondTeam(CombatBase $combat): int
    {
        if ($combat->round > $combat->roundLimit) {
            return $combat->team2->hasAliveMembers() ? 2 : 1;
        } elseif (!$combat->team1->hasAliveMembers()) {
            return 2;
        } elseif (!$combat->team2->hasAliveMembers()) {
             return 1;
        }
        return 0;
    }

    /**
     * Evaluate winner of combat
     * Team 1 wins if at least 1 of its members is alive after round limit
     */
    public static function firstTeamSurvives(CombatBase $combat): int
    {
        if ($combat->round > $combat->roundLimit) {
            return $combat->team1->hasAliveMembers() ? 1 : 2;
        } elseif (!$combat->team1->hasAliveMembers()) {
            return 2;
        } elseif (!$combat->team2->hasAliveMembers()) {
            return 1;
        }
        return 0;
    }
}
