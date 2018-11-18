<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use HeroesofAbenez\Combat\CombatActions\ICombatAction;

/**
 * CombatActionSelector
 *
 * @author Jakub Konečný
 * @property string $defaultAction
 */
final class CombatActionSelector implements ICombatActionSelector {
  use \Nette\SmartObject;

  /** @var string */
  protected $defaultAction = CombatLogEntry::ACTION_ATTACK;

  /**
   * @return string
   */
  public function getDefaultAction(): string {
    return $this->defaultAction;
  }

  /**
   * @param string $defaultAction
   */
  public function setDefaultAction(string $defaultAction): void {
    $this->defaultAction = $defaultAction;
  }

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
    return $this->defaultAction;
  }
}
?>