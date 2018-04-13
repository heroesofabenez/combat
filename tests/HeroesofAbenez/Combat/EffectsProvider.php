<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * EffectsProvider
 *
 * @author Jakub Konečný
 */
final class EffectsProvider implements ICharacterEffectsProvider {
  public function getCombatEffects(): array {
    return [new CharacterEffect([
      "id" => "provider1Effect",
      "type" => SkillSpecial::TYPE_BUFF,
      "source" => CharacterEffect::SOURCE_EQUIPMENT,
      "value" => 10,
      "duration" => CharacterEffect::DURATION_COMBAT,
      "stat" => SkillSpecial::STAT_HITPOINTS,
    ])];
  }
}
?>