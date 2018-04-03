<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * ICombatEffectProvider
 *
 * @author Jakub Konečný
 */
interface ICharacterEffectProvider {
  public function toCombatEffect(): ?CharacterEffect;
}
?>