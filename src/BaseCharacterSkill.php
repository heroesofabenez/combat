<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;

/**
 * Base character skill
 *
 * @author Jakub Konečný
 * @property-read BaseSkill $skill
 * @property int $level
 * @property-read int $cooldown
 * @property-read string $skillType
 */
abstract class BaseCharacterSkill {
  use \Nette\SmartObject;
  
  /** @var BaseSkill */
  protected $skill;
  /** @var int */
  protected $level;
  /** @var int */
  protected $cooldown = 0;
  
  public function __construct(BaseSkill $skill, int $level) {
    $this->skill = $skill;
    $this->setLevel($level);
    $this->resetCooldown();
  }

  abstract public function getSkillType(): string;
  
  public function getLevel(): int {
    return $this->level;
  }
  
  public function getCooldown(): int {
    return $this->cooldown;
  }
  
  public function setLevel(int $level): void {
    $this->level = Numbers::range($level, 0, $this->skill->levels);
  }
  
  public function canUse(): bool {
    return ($this->cooldown < 1);
  }
  
  public function resetCooldown(): void {
    $this->cooldown = $this->skill->cooldown;
  }
  
  public function decreaseCooldown(): void {
    if($this->cooldown > 0) {
      $this->cooldown--;
    }
  }
}
?>