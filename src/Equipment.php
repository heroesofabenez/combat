<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Nexendrie\Utils\Constants;
use Nexendrie\Utils\Numbers;

/**
 * Equipment
 *
 * @author Jakub Konečný
 * @property-read int $strength
 * @property int $durability
 */
class Equipment implements ICharacterEffectsProvider
{
    use \Nette\SmartObject;

    public const SLOT_WEAPON = "weapon";
    public const SLOT_ARMOR = "armor";
    public const SLOT_SHIELD = "shield";
    public const SLOT_AMULET = "amulet";
    public const SLOT_HELMET = "helmet";
    public const SLOT_RING = "ring";

    public readonly int $id;
    public readonly string $name;
    public readonly string $slot;
    public readonly ?string $type;
    public readonly int $rawStrength;
    public bool $worn;
    protected int $durability;
    public readonly int $maxDurability;

    public function __construct(array $data)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $data = $resolver->resolve($data);
        $this->id = $data["id"];
        $this->name = $data["name"];
        $this->slot = $data["slot"];
        $this->type = $data["type"];
        $this->rawStrength = $data["strength"];
        $this->worn = $data["worn"];
        $this->maxDurability = $data["maxDurability"];
        $this->durability = $data["durability"];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $allStats = ["id", "name", "slot", "type", "strength", "worn",];
        $resolver->setRequired($allStats);
        $resolver->setAllowedTypes("id", "integer");
        $resolver->setAllowedTypes("name", "string");
        $resolver->setAllowedTypes("slot", "string");
        $resolver->setAllowedValues("slot", function (string $value): bool {
            return in_array($value, $this->getAllowedSlots(), true);
        });
        $resolver->setAllowedTypes("type", "null");
        $resolver->setDefault("type", null);
        $resolver->setAllowedTypes("strength", "integer");
        $resolver->setAllowedValues("strength", function (int $value): bool {
            return ($value >= 0);
        });
        $resolver->setAllowedTypes("worn", "boolean");
        $resolver->setDefault("maxDurability", 0);
        $resolver->setAllowedTypes("maxDurability", "integer");
        $resolver->setAllowedValues("maxDurability", function (int $value): bool {
            return ($value >= 0);
        });
        $resolver->setDefault("durability", function (Options $options) {
            return $options["maxDurability"];
        });
        $resolver->setAllowedTypes("durability", "integer");
    }

    protected function getAllowedSlots(): array
    {
        return Constants::getConstantsValues(static::class, "SLOT_");
    }

    protected function getStrength(): int
    {
        if ($this->durability >= $this->maxDurability * 0.7) {
            return $this->rawStrength;
        } elseif ($this->durability >= $this->maxDurability / 2) {
            return (int) ($this->rawStrength * 0.75);
        } elseif ($this->durability >= $this->maxDurability / 4) {
            return (int) ($this->rawStrength / 2);
        } elseif ($this->durability >= $this->maxDurability / 10) {
            return (int) ($this->rawStrength / 4);
        }
        return 0;
    }

    protected function getDurability(): int
    {
        return $this->durability;
    }

    protected function setDurability(int $durability): void
    {
        $this->durability = Numbers::clamp($durability, 0, $this->maxDurability);
    }

    protected function getDeployParams(): array
    {
        $stat = [
            static::SLOT_WEAPON => Character::STAT_DAMAGE, static::SLOT_ARMOR => Character::STAT_DEFENSE,
            static::SLOT_HELMET => Character::STAT_MAX_HITPOINTS, static::SLOT_SHIELD => Character::STAT_DODGE,
            static::SLOT_AMULET => Character::STAT_INITIATIVE, static::SLOT_RING => Character::STAT_HIT,
        ];
        $return = [
            "id" => "equipment" . $this->id . "bonusEffect",
            "type" => SkillSpecial::TYPE_BUFF,
            "stat" => $stat[$this->slot],
            "value" => $this->strength,
            "valueAbsolute" => true,
            "duration" => CharacterEffect::DURATION_COMBAT,
        ];
        return $return;
    }

    public function getCombatEffects(): array
    {
        if (!$this->worn) {
            return [];
        }
        return [new CharacterEffect($this->getDeployParams())];
    }
}
