<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Skill attack
 *
 * @author Jakub Konečný
 */
final class SkillAttack extends BaseSkill
{
    public const string TARGET_SINGLE = "single";
    public const string TARGET_ROW = "row";
    public const string TARGET_COLUMN = "column";

    public readonly string $baseDamage;
    public readonly string $damageGrowth;
    public readonly int $strikes;
    public readonly ?string $hitRate;

    public function __construct(array $data)
    {
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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $allStats = ["baseDamage", "damageGrowth", "strikes", "hitRate",];
        $resolver->setRequired($allStats);
        $resolver->setAllowedTypes("baseDamage", "string");
        $resolver->setAllowedTypes("damageGrowth", "string");
        $resolver->setAllowedTypes("strikes", "integer");
        $resolver->setAllowedValues("strikes", function (int $value): bool {
            return ($value > 0);
        });
        $resolver->setAllowedTypes("hitRate", ["string", "null"]);
    }

    protected function getCooldown(): int
    {
        return 3;
    }
}
