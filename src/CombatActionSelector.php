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
    public function chooseAction(CombatBase $combat, Character $character): ?ICombatAction
    {
        if (!$character->canAct()) {
            return null;
        }
        /** @var ICombatAction[] $actions */
        $actions = $combat->combatActions->toArray();
        usort($actions, static fn(ICombatAction $a, ICombatAction $b): int => $a->getPriority() <=> $b->getPriority());
        foreach ($actions as $action) {
            if ($action->shouldUse($combat, $character)) {
                return $action;
            }
        }
        return null;
    }
}
