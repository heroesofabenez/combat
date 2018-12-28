<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Constants;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Weapon
 *
 * @author Jakub Konečný
 * @property-read bool $ranged
 * @property-read string $damageStat
 */
class Weapon extends Equipment {
  public const TYPE_SWORD = "sword";
  public const TYPE_AXE = "axe";
  public const TYPE_CLUB = "club";
  public const TYPE_DAGGER = "dagger";
  public const TYPE_SPEAR = "spear";
  public const TYPE_STAFF = "staff";
  public const TYPE_BOW = "bow";
  public const TYPE_CROSSBOW = "crossbow";
  public const TYPE_THROWING_KNIFE = "throwing knife";
  public const TYPE_INSTRUMENT = "instrument";
  public const MELEE_TYPES = [
    self::TYPE_SWORD, self::TYPE_AXE, self::TYPE_CLUB, self::TYPE_DAGGER, self::TYPE_SPEAR,
  ];
  public const RANGED_TYPES = [
    self::TYPE_STAFF, self::TYPE_BOW, self::TYPE_CROSSBOW, self::TYPE_THROWING_KNIFE, self::TYPE_INSTRUMENT,
  ];
  
  public function isRanged(): bool {
    return in_array($this->type, static::RANGED_TYPES, true);
  }

  public function getDamageStat(): string {
    switch($this->type) {
      case static::TYPE_STAFF:
        return Character::STAT_INTELLIGENCE;
      case static::TYPE_CLUB:
        return Character::STAT_CONSTITUTION;
      case static::TYPE_BOW:
      case static::TYPE_THROWING_KNIFE:
        return Character::STAT_DEXTERITY;
      case static::TYPE_INSTRUMENT:
        return Character::STAT_CHARISMA;
      default:
        return Character::STAT_STRENGTH;
    }
  }
  
  protected function configureOptions(OptionsResolver $resolver): void {
    parent::configureOptions($resolver);
    $resolver->setAllowedTypes("type", "string");
    $resolver->setAllowedValues("type", function(string $value) {
      return in_array($value, $this->getAllowedTypes(), true);
    });
  }
  
  protected function getAllowedTypes(): array {
    return Constants::getConstantsValues(static::class, "TYPE_");
  }
}
?>