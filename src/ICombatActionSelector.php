<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ICombatActionSelector
 *
 * @author Jakub Konečný
 */
interface ICombatActionSelector {
  public function chooseAction(CombatBase $combat, Character $character): ?ICombatAction;
}
?>