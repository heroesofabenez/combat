<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * Character skill attack
 *
 * @author Jakub Konečný
 * @property-read SkillAttack $skill
 * @property-read int $damage
 * @property-read int $hitRate
 */
final class CharacterAttackSkill extends BaseCharacterSkill {
  /** @var SkillAttack */
  protected $skill;
  
  public function __construct(SkillAttack $skill, int $level) {
    parent::__construct($skill, $level);
  }

  public function getSkillType(): string {
    return "attack";
  }

  /**
   * @return SkillAttack
   */
  public function getSkill(): SkillAttack {
    return $this->skill;
  }
  
  public function getDamage(): int {
    $damage = 0;
    if(substr($this->skill->baseDamage, -1) === "%") {
      $damage += (int) $this->skill->baseDamage;
    }
    if(substr($this->skill->damageGrowth, -1) === "%") {
      $damage += (int) $this->skill->damageGrowth * ($this->level - 1);
    }
    return $damage;
  }
  
  public function getHitRate(): int {
    if(is_string($this->skill->hitRate) && substr($this->skill->hitRate, -1) === "%") {
      return (int) $this->skill->hitRate;
    }
    return 100;
  }
}
?>