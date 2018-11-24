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
  
  public function isRanged(): bool {
    return in_array($this->type, [
      static::TYPE_STAFF, static::TYPE_BOW, static::TYPE_CROSSBOW, static::TYPE_THROWING_KNIFE,
    ], true);
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