<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Constants;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Data structure for combat action
 *
 * @author Jakub Konečný
 * @property-read Character $character1
 * @property-read Character $character2
 * @property-read string $action
 * @property-read string $name
 * @property-read bool $result
 * @property-read int $amount
 */
class CombatLogEntry {
  use \Nette\SmartObject;
  
  public const ACTION_ATTACK = "attack";
  public const ACTION_SKILL_ATTACK = "skill_attack";
  public const ACTION_SKILL_SPECIAL = "skill_special";
  public const ACTION_HEALING = "healing";
  public const ACTION_POISON = "poison";

  /** @var Character */
  protected $character1;
  /** @var Character */
  protected $character2;
  /** @var  string */
  protected $action;
  /** @var string */
  protected $name;
  /** @var bool */
  protected $result;
  /** @var int */
  protected $amount;
  
  public function __construct(array $action) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $action = $resolver->resolve($action);
    $this->action = $action["action"];
    $this->result = $action["result"];
    $this->amount = $action["amount"];
    $this->character1 = clone $action["character1"];
    $this->character2 = clone $action["character2"];
    $this->name = $action["name"];
  }

  protected function configureOptions(OptionsResolver $resolver): void {
    $requiredStats = ["action", "result", "character1", "character2",];
    $resolver->setDefined(["amount", "name",]);
    $resolver->setRequired($requiredStats);
    $resolver->setAllowedTypes("action", "string");
    $resolver->setAllowedValues("action", function(string $value) {
      return in_array($value, $this->getAllowedActions(), true);
    });
    $resolver->setAllowedTypes("result", "bool");
    $resolver->setAllowedTypes("amount", "integer");
    $resolver->setDefault("amount", 0);
    $resolver->setAllowedTypes("name", "string");
    $resolver->setDefault("name", "");
    $resolver->setAllowedTypes("character1", Character::class);
    $resolver->setAllowedTypes("character2", Character::class);
  }

  /**
   * @return string[]
   */
  protected function getAllowedActions(): array {
    return Constants::getConstantsValues(static::class, "ACTION_");
  }
  
  public function getCharacter1(): Character {
    return $this->character1;
  }
  
  public function getCharacter2(): Character {
    return $this->character2;
  }
  
  public function getAction(): string {
    return $this->action;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  public function isResult(): bool {
    return $this->result;
  }
  
  public function getAmount(): int {
    return $this->amount;
  }
}
?>