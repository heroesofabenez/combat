<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 */
final class CombatActionSelector implements ICombatActionSelector {
  use \Nette\SmartObject;

  public function chooseAction(CombatBase $combat, Character $character): ?ICombatAction {
    if($character->hitpoints < 1) {
      return null;
    }
    /** @var ICombatAction[] $actions */
    $actions = $combat->combatActions->toArray();
    usort($actions, function(ICombatAction $a, ICombatAction $b): bool {
      return $a->getPriority() < $b->getPriority();
    });
    foreach($actions as $action) {
      if($action->shouldUse($combat, $character)) {
        return $action;
      }
    }
    return null;
  }
}
?>