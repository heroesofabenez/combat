<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Nexendrie\Utils\Constants;

/**
 * Skill special
 *
 * @author Jakub Konečný
 * @property-read string $type
 * @property-read string|null $stat
 * @property-read int $value
 * @property-read int $valueGrowth
 * @property-read int $duration
 */
final class SkillSpecial extends BaseSkill {
  public const TYPE_BUFF = "buff";
  public const TYPE_DEBUFF = "debuff";
  public const TYPE_STUN = "stun";
  public const TYPE_POISON = "poison";
  public const TYPE_HIDE = "hide";
  public const TARGET_SELF = "self";
  public const TARGET_ENEMY = "enemy";
  public const TARGET_PARTY = "party";
  public const TARGET_ENEMY_PARTY = "enemy_party";
  /** @var string[] */
  public const NO_STAT_TYPES = [self::TYPE_STUN, self::TYPE_POISON, self::TYPE_HIDE, ];

  private string $type;
  private ?string $stat;
  private int $value;
  private int $valueGrowth;
  private int $duration;
  
  public function __construct(array $data) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $data = $resolver->resolve($data);
    $this->id = $data["id"];
    $this->name = $data["name"];
    $this->type = $data["type"];
    $this->target = $data["target"];
    $this->stat = $data["stat"];
    $this->value = $data["value"];
    $this->valueGrowth = $data["valueGrowth"];
    $this->levels = $data["levels"];
    $this->duration = $data["duration"];
  }
  
  protected function configureOptions(OptionsResolver $resolver): void {
    parent::configureOptions($resolver);
    $allStats = ["type", "stat", "value", "valueGrowth", "duration", ];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("type", "string");
    $resolver->setAllowedValues("type", function(string $value): bool {
      return in_array($value, $this->getAllowedTypes(), true);
    });
    $resolver->setAllowedTypes("stat", ["string", "null"]);
    $resolver->setAllowedValues("stat", function(?string $value): bool {
      return $value === null || in_array($value, $this->getAllowedStats(), true);
    });
    $resolver->setAllowedTypes("value", "integer");
    $resolver->setAllowedValues("value", function(int $value): bool {
      return ($value >= 0);
    });
    $resolver->setAllowedTypes("valueGrowth", "integer");
    $resolver->setAllowedValues("valueGrowth", function(int $value): bool {
      return ($value >= 0);
    });
    $resolver->setAllowedTypes("duration", "integer");
    $resolver->setAllowedValues("duration", function(int $value): bool {
      return ($value >= 0);
    });
  }
  
  protected function getAllowedTypes(): array {
    return Constants::getConstantsValues(self::class, "TYPE_");
  }
  
  protected function getAllowedStats(): array {
    return Character::SECONDARY_STATS;
  }
  
  protected function getCooldown(): int {
    return 5;
  }
  
  protected function getType(): string {
    return $this->type;
  }
  
  protected function getStat(): ?string {
    return $this->stat;
  }
  
  protected function getValue(): int {
    return $this->value;
  }
  
  protected function getValueGrowth(): int {
    return $this->valueGrowth;
  }
  
  protected function getDuration(): int {
    return $this->duration;
  }
}
?>