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
 * @property-read bool $valueAbsolute
 * @property int|string $duration
 * @method void onApply(Character $character, CharacterEffect $effect)
 * @method void onRemove(Character $character, CharacterEffect $effect)
 */
class CharacterEffect {
  use \Nette\SmartObject;

  public const DURATION_COMBAT = "combat";
  public const DURATION_FOREVER = "forever";

  protected string $id;
  protected string $type;
  protected string $stat = "";
  protected int $value = 0;
  protected bool $valueAbsolute;
  /** @var int|string */
  protected $duration;
  /** @var callable[] */
  public array $onApply = [];
  /** @var callable[] */
  public array $onRemove = [];
  
  public function __construct(array $effect) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $effect = $resolver->resolve($effect);
    if(!in_array($effect["type"], SkillSpecial::NO_STAT_TYPES, true) && $effect["stat"] === "") {
      throw new \InvalidArgumentException("The option stat with value '' is invalid.");
    }
    $this->id = $effect["id"];
    $this->type = $effect["type"];
    $this->stat = $effect["stat"];
    $this->value = $effect["value"];
    $this->valueAbsolute = $effect["valueAbsolute"];
    $this->duration = $effect["duration"];
    $this->registerDefaultHandlers();
  }

  protected function registerDefaultHandlers(): void {
    $this->onApply[] = function(Character $character, self $effect): void {
      $character->recalculateStats();
      if($effect->stat === Character::STAT_MAX_HITPOINTS) {
        $character->heal($effect->value);
      }
    };
    $this->onRemove[] = function(Character $character, self $effect): void {
      $character->recalculateStats();
      if($effect->stat === Character::STAT_MAX_HITPOINTS) {
        $character->harm($effect->value);
      }
    };
  }

  protected function configureOptions(OptionsResolver $resolver): void {
    $allStats = ["id", "type", "value", "valueAbsolute", "duration", "stat", ];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("id", "string");
    $resolver->setAllowedTypes("type", "string");
    $resolver->setAllowedValues("type", function(string $value): bool {
      return in_array($value, $this->getAllowedTypes(), true);
    });
    $resolver->setAllowedTypes("stat", "string");
    $resolver->setDefault("stat", "");
    $resolver->setAllowedValues("stat", function(string $value): bool {
      return $value === "" || in_array($value, $this->getAllowedStats(), true);
    });
    $resolver->setAllowedTypes("value", "integer");
    $resolver->setAllowedTypes("valueAbsolute", "bool");
    $resolver->setDefault("value", 0);
    $resolver->setAllowedTypes("duration", ["string", "integer"]);
    $resolver->setAllowedValues("duration", function($value): bool {
      return (in_array($value, $this->getDurations(), true)) || ($value > 0);
    });
  }
  
  protected function getAllowedStats(): array {
    return Constants::getConstantsValues(Character::class, "STAT_");
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
  
  protected function getId(): string {
    return $this->id;
  }
  
  protected function getType(): string {
    return $this->type;
  }
  
  protected function getStat(): string {
    return $this->stat;
  }
  
  protected function getValue(): int {
    return $this->value;
  }

  protected function isValueAbsolute(): bool {
    return $this->valueAbsolute;
  }

  /**
   * @return int|string
   */
  protected function getDuration() {
    return $this->duration;
  }
  
  /**
   * @param string|int $value
   * @throws \InvalidArgumentException
   */
  protected function setDuration($value): void {
    if(!is_int($value) && !in_array($value, $this->getDurations(), true)) {
      throw new \InvalidArgumentException("Invalid value set to CharacterEffect::\$duration. Expected string or integer.");
    }
    $this->duration = $value;
  }
}
?>