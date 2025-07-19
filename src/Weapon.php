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
  
  protected function isRanged(): bool {
    return in_array($this->type, static::RANGED_TYPES, true);
  }

  protected function getDamageStat(): string {
    return match($this->type) {
      static::TYPE_STAFF => Character::STAT_INTELLIGENCE,
      static::TYPE_CLUB => Character::STAT_CONSTITUTION,
      static::TYPE_BOW, static::TYPE_THROWING_KNIFE => Character::STAT_DEXTERITY,
      static::TYPE_INSTRUMENT => Character::STAT_CHARISMA,
      default => Character::STAT_STRENGTH,
    };
  }
  
  protected function configureOptions(OptionsResolver $resolver): void {
    parent::configureOptions($resolver);
    $resolver->setAllowedTypes("type", "string");
    $resolver->setAllowedValues("type", function(string $value): bool {
      return in_array($value, $this->getAllowedTypes(), true);
    });
  }
  
  protected function getAllowedTypes(): array {
    return Constants::getConstantsValues(static::class, "TYPE_");
  }
}
?>