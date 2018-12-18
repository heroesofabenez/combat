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
  
  /** @var int */
  protected $id;
  /** @var string */
  protected $name;
  /** @var string */
  protected $target;
  /** @var int */
  protected $levels;
  
  protected function configureOptions(OptionsResolver $resolver): void {
    $resolver->setRequired(["id", "name", "target", "levels",]);
    $resolver->setAllowedTypes("id", "int");
    $resolver->setAllowedTypes("name", "string");
    $resolver->setAllowedTypes("target", "string");
    $resolver->setAllowedValues("target", function(string $value) {
      return in_array($value, $this->getAllowedTargets(), true);
    });
    $resolver->setAllowedTypes("levels", "integer");
    $resolver->setAllowedValues("levels", function(int $value) {
      return ($value > 0);
    });
  }
  
  protected function getAllowedTargets(): array {
    return Constants::getConstantsValues(static::class, "TARGET_");
  }
  
  abstract public function getCooldown(): int;
  
  public function getId(): int {
    return $this->id;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  public function getTarget(): string {
    return $this->target;
  }
  
  public function getLevels(): int {
    return $this->levels;
  }
}
?>