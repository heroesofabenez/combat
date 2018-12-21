<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use HeroesofAbenez\Combat\CombatActions\ICombatAction;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 */
final class CombatActionSelector implements ICombatActionSelector {
  use \Nette\SmartObject;

  public function chooseAction(CombatBase $combat, Character $character): ?string {
    if($character->hitpoints < 1) {
      return null;
    }
    /** @var ICombatAction[] $actions */
    $actions = $combat->combatActions->toArray();
    usort($actions, function(ICombatAction $a, ICombatAction $b) {
      return $a->getPriority() < $b->getPriority();
    });
    foreach($actions as $action) {
      if($action->shouldUse($combat, $character)) {
        return $action->getName();
      }
    }
    return null;
  }
}
?>