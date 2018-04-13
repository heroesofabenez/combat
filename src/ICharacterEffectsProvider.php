<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ICombatEffectsProvider
 *
 * @author Jakub Konečný
 */
interface ICharacterEffectsProvider {
  /** @return CharacterEffect[] */
  public function getCombatEffects(): array;
}
?>