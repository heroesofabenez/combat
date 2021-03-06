<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Nexendrie\Utils\Constants;

/**
 * Base Skill
 *
 * @author Jakub Konečný
 * @property-read int $id
 * @property-read string $name
 * @property-read string $target
 * @property-read int $levels
 * @property-read int $cooldown
 */
abstract class BaseSkill {
  use \Nette\SmartObject;

  protected int $id;
  protected string $name;
  protected string $target;
  protected int $levels;
  
  protected function configureOptions(OptionsResolver $resolver): void {
    $resolver->setRequired(["id", "name", "target", "levels", ]);
    $resolver->setAllowedTypes("id", "int");
    $resolver->setAllowedTypes("name", "string");
    $resolver->setAllowedTypes("target", "string");
    $resolver->setAllowedValues("target", function(string $value): bool {
      return in_array($value, $this->getAllowedTargets(), true);
    });
    $resolver->setAllowedTypes("levels", "integer");
    $resolver->setAllowedValues("levels", function(int $value): bool {
      return ($value > 0);
    });
  }
  
  protected function getAllowedTargets(): array {
    return Constants::getConstantsValues(static::class, "TARGET_");
  }
  
  abstract protected function getCooldown(): int;
  
  protected function getId(): int {
    return $this->id;
  }
  
  protected function getName(): string {
    return $this->name;
  }
  
  protected function getTarget(): string {
    return $this->target;
  }
  
  protected function getLevels(): int {
    return $this->levels;
  }
}
?>