<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Constants;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Data structure for effect on character
 *
 * @author Jakub Konečný
 * @property-read string $id
 * @property-read string $type
 * @property-read string $stat
 * @property-read int $value
 * @property-read string $source
 * @property int|string $duration
 * @method void onApply(Character $character, CharacterEffect $effect)
 * @method void onRemove(Character $character, CharacterEffect $effect)
 */
class CharacterEffect {
  use \Nette\SmartObject;
  
  public const SOURCE_PET = "pet";
  public const SOURCE_SKILL = "skill";
  public const SOURCE_EQUIPMENT = "equipment";
  public const DURATION_COMBAT = "combat";
  public const DURATION_FOREVER = "forever";
  
  /** @var string */
  protected $id;
  /** @var string */
  protected $type;
  /** @var string */
  protected $stat = "";
  /** @var int */
  protected $value = 0;
  /** @var string */
  protected $source;
  /** @var int|string */
  protected $duration;
  /** @var callable[] */
  public $onApply = [];
  /** @var callable[] */
  public $onRemove = [];
  
  public function __construct(array $effect) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $effect = $resolver->resolve($effect);
    if(!in_array($effect["type"], SkillSpecial::NO_STAT_TYPES, true) AND $effect["stat"] === "") {
      throw new \InvalidArgumentException("The option stat with value '' is invalid.");
    }
    $this->id = $effect["id"];
    $this->type = $effect["type"];
    $this->stat = $effect["stat"];
    $this->value = $effect["value"];
    $this->source = $effect["source"];
    $this->duration = $effect["duration"];
    $this->onApply[] = function(Character $character, self $effect) {
      $character->recalculateStats();
      if($effect->stat === Character::STAT_MAX_HITPOINTS) {
        $character->heal($effect->value);
      }
    };
    $this->onRemove[] = function(Character $character, self $effect) {
      $character->recalculateStats();
      if($effect->stat === Character::STAT_MAX_HITPOINTS) {
        $character->harm($effect->value);
      }
    };
  }

  protected function configureOptions(OptionsResolver $resolver): void {
    $allStats = ["id", "type", "source", "value", "duration", "stat",];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("id", "string");
    $resolver->setAllowedTypes("type", "string");
    $resolver->setAllowedValues("type", function(string $value) {
      return in_array($value, $this->getAllowedTypes(), true);
    });
    $resolver->setAllowedTypes("stat", "string");
    $resolver->setDefault("stat", "");
    $resolver->setAllowedValues("stat", function(string $value) {
      return $value === "" OR in_array($value, $this->getAllowedStats(), true);
    });
    $resolver->setAllowedTypes("source", "string");
    $resolver->setAllowedValues("source", function(string $value) {
      return in_array($value, $this->getAllowedSources(), true);
    });
    $resolver->setAllowedTypes("value", "integer");
    $resolver->setDefault("value", 0);
    $resolver->setAllowedTypes("duration", ["string", "integer"]);
    $resolver->setAllowedValues("duration", function($value) {
      return (in_array($value, $this->getDurations(), true)) OR ($value > 0);
    });
  }
  
  protected function getAllowedStats(): array {
    return Constants::getConstantsValues(Character::class, "STAT_");
  }
  
  /**
   * @return string[]
   */
  protected function getAllowedSources(): array {
    return Constants::getConstantsValues(static::class, "SOURCE_");
  }
  
  /**
   * @return string[]
   */
  protected function getAllowedTypes(): array {
    return Constants::getConstantsValues(SkillSpecial::class, "TYPE_");
  }
  
  /**
   * @return string[]
   */
  protected function getDurations(): array {
    return Constants::getConstantsValues(static::class, "DURATION_");
  }
  
  public function getId(): string {
    return $this->id;
  }
  
  public function getType(): string {
    return $this->type;
  }
  
  public function getStat(): string {
    return $this->stat;
  }
  
  public function getValue(): int {
    return $this->value;
  }
  
  public function getSource(): string {
    return $this->source;
  }
  
  /**
   * @return int|string
   */
  public function getDuration() {
    return $this->duration;
  }
  
  /**
   * @param string|int $value
   * @throws \InvalidArgumentException
   */
  public function setDuration($value): void {
    if(!is_int($value) AND !in_array($value, $this->getDurations(), true)) {
      throw new \InvalidArgumentException("Invalid value set to CharacterEffect::\$duration. Expected string or integer.");
    }
    $this->duration = $value;
  }
}
?>