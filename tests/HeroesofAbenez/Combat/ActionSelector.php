<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ActionSelector
 *
 * @author Jakub Konečný
 */
final class ActionSelector implements ICombatActionSelector {
  public function chooseAction(CombatBase $combat, Character $character): ?string {
    return null;
  }
}
?>