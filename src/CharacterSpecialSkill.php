<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * Character skill special
 *
 * @author Jakub Konečný
 * @property-read SkillSpecial $skill
 * @property-read int $value
 */
final class CharacterSpecialSkill extends BaseCharacterSkill
{
    public function __construct(SkillSpecial $skill, int $level)
    {
        parent::__construct($skill, $level);
    }

    protected function getSkillType(): string
    {
        return "special";
    }

    protected function getSkill(): SkillSpecial
    {
        return $this->skill;
    }

    protected function getValue(): int
    {
        if ($this->skill->type === SkillSpecial::TYPE_STUN) {
            return 0;
        }
        $value = $this->skill->value;
        $value += $this->skill->valueGrowth * ($this->level - 1);
        return $value;
    }
}
