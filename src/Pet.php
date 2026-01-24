<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Pet
 *
 * @author Jakub Konečný
 */
final class Pet implements ICharacterEffectsProvider
{
    public readonly int $id;
    public bool $deployed;
    public readonly string $bonusStat;
    public readonly int $bonusValue;

    public function __construct(array $data)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $data = $resolver->resolve($data);
        $this->id = $data["id"];
        $this->deployed = $data["deployed"];
        $this->bonusStat = $data["bonusStat"];
        $this->bonusValue = $data["bonusValue"];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $allStats = ["id", "deployed", "bonusStat", "bonusValue",];
        $resolver->setRequired($allStats);
        $resolver->setAllowedTypes("id", "integer");
        $resolver->setAllowedTypes("deployed", "boolean");
        $resolver->setAllowedTypes("bonusStat", "string");
        $resolver->setAllowedValues(
            "bonusStat",
            static fn(string $value): bool => in_array($value, Character::BASE_STATS, true)
        );
        $resolver->setAllowedTypes("bonusValue", "integer");
        $resolver->setAllowedValues("bonusValue", static fn(int $value): bool => ($value >= 0));
    }

    protected function getDeployParams(): array
    {
        return [
            "id" => "pet" . $this->id . "bonusEffect",
            "type" => SkillSpecial::TYPE_BUFF,
            "stat" => $this->bonusStat,
            "value" => $this->bonusValue,
            "valueAbsolute" => false,
            "duration" => CharacterEffect::DURATION_COMBAT,
        ];
    }

    public function getCombatEffects(): array
    {
        if (!$this->deployed) {
            return [];
        }
        return [new CharacterEffect($this->getDeployParams())];
    }
}
