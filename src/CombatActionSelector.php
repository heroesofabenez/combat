<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 */
final class CombatActionSelector implements ICombatActionSelector
{
    public function chooseAction(CombatBase $combat, Character $character): ?CombatAction
    {
        if (!$character->canAct()) {
            return null;
        }
        /** @var CombatAction[] $actions */
        $actions = $combat->combatActions->toArray();
        usort($actions, static fn(CombatAction $a, CombatAction $b): int => $a->getPriority() <=> $b->getPriority());
        foreach ($actions as $action) {
            if ($action->shouldUse($combat, $character)) {
                return $action;
            }
        }
        return null;
    }
}
