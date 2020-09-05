<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Skill attack
 *
 * @author Jakub Konečný
 * @property-read string $baseDamage
 * @property-read string $damageGrowth
 * @property-read int $strikes
 * @property-read string|null $hitRate
 */
final class SkillAttack extends BaseSkill {
  public const TARGET_SINGLE = "single";
  public const TARGET_ROW = "row";
  public const TARGET_COLUMN = "column";

  protected string $baseDamage;
  protected string $damageGrowth;
  protected int $strikes;
  protected ?string $hitRate;
  
  public function __construct(array $data) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $data = $resolver->resolve($data);
    $this->id = $data["id"];
    $this->name = $data["name"];
    $this->baseDamage = $data["baseDamage"];
    $this->damageGrowth = $data["damageGrowth"];
    $this->levels = $data["levels"];
    $this->target = $data["target"];
    $this->strikes = $data["strikes"];
    $this->hitRate = $data["hitRate"];
  }
  
  protected function configureOptions(OptionsResolver $resolver): void {
    parent::configureOptions($resolver);
    $allStats = ["baseDamage", "damageGrowth", "strikes", "hitRate", ];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("baseDamage", "string");
    $resolver->setAllowedTypes("damageGrowth", "string");
    $resolver->setAllowedTypes("strikes", "integer");
    $resolver->setAllowedValues("strikes", function(int $value): bool {
      return ($value > 0);
    });
    $resolver->setAllowedTypes("hitRate", ["string", "null"]);
  }
  
  protected function getCooldown(): int {
    return 3;
  }
  
  protected function getBaseDamage(): string {
    return $this->baseDamage;
  }
  
  protected function getDamageGrowth(): string {
    return $this->damageGrowth;
  }
  
  protected function getStrikes(): int {
    return $this->strikes;
  }
  
  protected function getHitRate(): ?string {
    return $this->hitRate;
  }
}
?>