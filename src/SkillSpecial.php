<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Nexendrie\Utils\Constants;

/**
 * Skill special
 *
 * @author Jakub Konečný
 */
final class SkillSpecial extends BaseSkill
{
    public const string TYPE_BUFF = "buff";
    public const string TYPE_DEBUFF = "debuff";
    public const string TYPE_STUN = "stun";
    public const string TYPE_POISON = "poison";
    public const string TYPE_HIDE = "hide";
    public const string TARGET_SELF = "self";
    public const string TARGET_ENEMY = "enemy";
    public const string TARGET_PARTY = "party";
    public const string TARGET_ENEMY_PARTY = "enemy_party";
    /** @var string[] */
    public const array NO_STAT_TYPES = [self::TYPE_STUN, self::TYPE_POISON, self::TYPE_HIDE,];

    public readonly string $type;
    public readonly ?string $stat;
    public readonly int $value;
    public readonly int $valueGrowth;
    public readonly int $duration;

    public function __construct(array $data)
    {
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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $allStats = ["type", "stat", "value", "valueGrowth", "duration",];
        $resolver->setRequired($allStats);
        $resolver->setAllowedTypes("type", "string");
        $resolver->setAllowedValues(
            "type",
            static fn(string $value): bool => in_array($value, Constants::getConstantsValues(self::class, "TYPE_"), true)
        );
        $resolver->setAllowedTypes("stat", ["string", "null"]);
        $resolver->setAllowedValues(
            "stat",
            static fn(?string $value): bool => $value === null || in_array($value, Character::SECONDARY_STATS, true)
        );
        $resolver->setAllowedTypes("value", "integer");
        $resolver->setAllowedValues("value", static fn(int $value): bool => ($value >= 0));
        $resolver->setAllowedTypes("valueGrowth", "integer");
        $resolver->setAllowedValues("valueGrowth", static fn(int $value): bool => ($value >= 0));
        $resolver->setAllowedTypes("duration", "integer");
        $resolver->setAllowedValues("duration", static fn(int $value): bool => ($value >= 0));
    }

    protected function getCooldown(): int
    {
        return 5;
    }
}
