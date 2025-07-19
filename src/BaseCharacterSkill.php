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
 * @property-read bool $usable
 */
abstract class BaseCharacterSkill {
  use \Nette\SmartObject;

  protected int $level;
  protected int $cooldown = 0;
  
  public function __construct(protected BaseSkill $skill, int $level) {
    $this->setLevel($level);
    $this->resetCooldown();
  }

  abstract protected function getSkillType(): string;
  
  protected function getLevel(): int {
    return $this->level;
  }
  
  protected function getCooldown(): int {
    return $this->cooldown;
  }
  
  protected function setLevel(int $level): void {
    $this->level = Numbers::range($level, 0, $this->skill->levels);
  }
  
  protected function isUsable(): bool {
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