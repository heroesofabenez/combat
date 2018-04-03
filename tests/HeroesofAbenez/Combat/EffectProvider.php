<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * EffectProvider
 *
 * @author Jakub Konečný
 */
class EffectProvider implements ICharacterEffectProvider {
  public function toCombatEffect(): ?CharacterEffect {
    return new CharacterEffect([
      "id" => "provider1Effect",
      "type" => SkillSpecial::TYPE_BUFF,
      "source" => CharacterEffect::SOURCE_EQUIPMENT,
      "value" => 10,
      "duration" => CharacterEffect::DURATION_COMBAT,
      "stat" => SkillSpecial::STAT_HITPOINTS,
    ]);
  }
}
?>