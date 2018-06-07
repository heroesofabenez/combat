<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Nexendrie\Utils\Constants;

/**
 * Equipment
 *
 * @author Jakub Konečný
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slot
 * @property-read string|null $type
 * @property-read int $strength
 * @property bool $worn Is the item worn?
 */
class Equipment implements ICharacterEffectsProvider {
  use \Nette\SmartObject;
  
  public const SLOT_WEAPON = "weapon";
  public const SLOT_ARMOR = "armor";
  public const SLOT_SHIELD = "shield";
  public const SLOT_AMULET = "amulet";
  public const SLOT_HELMET = "helmet";
  
  /** @var int */
  protected $id;
  /** @var string */
  protected $name;
  /** @var string */
  protected $slot;
  /** @var string|null */
  protected $type;
  /** @var int */
  protected $strength;
  /** @var bool */
  protected $worn;
  
  public function __construct(array $data) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $data = $resolver->resolve($data);
    $this->id = $data["id"];
    $this->name = $data["name"];
    $this->slot = $data["slot"];
    $this->type = $data["type"];
    $this->strength = $data["strength"];
    $this->worn = $data["worn"];
  }
  
  protected function configureOptions(OptionsResolver $resolver): void {
    $allStats = ["id", "name", "slot", "type", "strength", "worn",];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("id", "integer");
    $resolver->setAllowedTypes("name", "string");
    $resolver->setAllowedTypes("slot", "string");
    $resolver->setAllowedValues("slot", function(string $value) {
      return in_array($value, $this->getAllowedSlots(), true);
    });
    $resolver->setAllowedTypes("type", "null");
    $resolver->setDefault("type", null);
    $resolver->setAllowedTypes("strength", "integer");
    $resolver->setAllowedValues("strength", function(int $value) {
      return ($value >= 0);
    });
    $resolver->setAllowedTypes("worn", "boolean");
  }
  
  protected function getAllowedSlots(): array {
    return Constants::getConstantsValues(static::class, "SLOT_");
  }
  
  public function getId(): int {
    return $this->id;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  public function getSlot(): string {
    return $this->slot;
  }
  
  public function getType(): ?string {
    return $this->type;
  }
  
  public function getStrength(): int {
    return $this->strength;
  }
  
  public function isWorn(): bool {
    return $this->worn;
  }
  
  public function setWorn(bool $worn): void {
    $this->worn = $worn;
  }
  
  protected function getDeployParams(): array {
    $stat = [
      static::SLOT_WEAPON => Character::STAT_DAMAGE, static::SLOT_ARMOR => Character::STAT_DEFENSE,
      static::SLOT_HELMET => Character::STAT_MAX_HITPOINTS, static::SLOT_SHIELD => Character::STAT_DODGE,
      static::SLOT_AMULET => Character::STAT_INITIATIVE,
    ];
    $return = [
      "id" => "equipment" . $this->id . "bonusEffect",
      "type" => SkillSpecial::TYPE_BUFF,
      "stat" => $stat[$this->slot],
      "value" => $this->strength,
      "source" => CharacterEffect::SOURCE_EQUIPMENT,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ];
    return $return;
  }
  
  public function getCombatEffects(): array {
    if(!$this->worn) {
      return [];
    }
    return [new CharacterEffect($this->getDeployParams())];
  }
}
?>