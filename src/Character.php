<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Structure for single character
 *
 * @author Jakub Konečný
 * @property-read int|string $id
 * @property-read string $name
 * @property-read string $gender
 * @property-read string $race
 * @property-read string $occupation
 * @property-read string $specialization
 * @property-read int $level
 * @property-read int $strength
 * @property-read int $strengthBase
 * @property-read int $dexterity
 * @property-read int $dexterityBase
 * @property-read int $constitution
 * @property-read int $constitutionBase
 * @property-read int $intelligence
 * @property-read int $intelligenceBase
 * @property-read int $charisma
 * @property-read int $charismaBase
 * @property-read int $maxHitpoints
 * @property-read int $maxHitpointsBase
 * @property-read int $hitpoints
 * @property-read int $damage
 * @property-read int $damageBase
 * @property-read int $hit
 * @property-read int $hitBase
 * @property-read int $dodge
 * @property-read int $dodgeBase
 * @property-read int $initiative
 * @property-read int $initiativeBase
 * @property-read string $initiativeFormula
 * @property-read int $defense
 * @property-read int $defenseBase
 * @property-read int|null $activePet
 * @property-read bool $stunned
 * @property-read bool $poisoned
 * @property-read bool $hidden
 * @property-read BaseCharacterSkill[] $usableSkills
 * @property IInitiativeFormulaParser $initiativeFormulaParser
 * @property int $positionRow
 * @property int $positionColumn
 */
class Character
{
    use \Nette\SmartObject;

    public const int HITPOINTS_PER_CONSTITUTION = 5;
    public const string STAT_STRENGTH = "strength";
    public const string STAT_DEXTERITY = "dexterity";
    public const string STAT_CONSTITUTION = "constitution";
    public const string STAT_INTELLIGENCE = "intelligence";
    public const string STAT_CHARISMA = "charisma";
    public const string STAT_MAX_HITPOINTS = "maxHitpoints";
    public const string STAT_DAMAGE = "damage";
    public const string STAT_DEFENSE = "defense";
    public const string STAT_HIT = "hit";
    public const string STAT_DODGE = "dodge";
    public const string STAT_INITIATIVE = "initiative";
    public const array BASE_STATS = [
        self::STAT_STRENGTH,
        self::STAT_DEXTERITY,
        self::STAT_CONSTITUTION,
        self::STAT_INTELLIGENCE,
        self::STAT_CHARISMA,
    ];
    public const array SECONDARY_STATS = [
        self::STAT_MAX_HITPOINTS,
        self::STAT_DAMAGE,
        self::STAT_DEFENSE,
        self::STAT_HIT,
        self::STAT_DODGE,
        self::STAT_INITIATIVE,
    ];
    public const string STATUS_STUNNED = "stunned";
    public const string STATUS_POISONED = "poisoned";
    public const string STATUS_HIDDEN = "hidden";

    protected int|string $id;
    protected string $name;
    protected string $gender = "male";
    protected string $race;
    protected string $occupation;
    protected string $specialization;
    protected int $level;
    protected int $strength;
    protected int $strengthBase;
    protected int $dexterity;
    protected int $dexterityBase;
    protected int $constitution;
    protected int $constitutionBase;
    protected int $intelligence;
    protected int $intelligenceBase;
    protected int $charisma;
    protected int $charismaBase;
    protected int $maxHitpoints;
    protected int $maxHitpointsBase;
    protected int $hitpoints;
    protected int $damage = 0;
    protected int $damageBase = 0;
    protected int $hit = 0;
    protected int $hitBase = 0;
    protected int $dodge = 0;
    protected int $dodgeBase = 0;
    protected int $initiative = 0;
    protected int $initiativeBase = 0;
    protected string $initiativeFormula;
    protected IInitiativeFormulaParser $initiativeFormulaParser;
    protected float $defense = 0;
    protected float $defenseBase = 0;
    /** @var Equipment[]|EquipmentCollection Character's equipment */
    public EquipmentCollection $equipment;
    /** @var Pet[]|PetsCollection Character's pets */
    public PetsCollection $pets;
    /** @var BaseCharacterSkill[]|CharacterSkillsCollection Character's skills */
    public CharacterSkillsCollection $skills;
    protected ?int $activePet = null;
    /** @var CharacterEffect[]|CharacterEffectsCollection Active effects */
    public CharacterEffectsCollection $effects;
    /** @var ICharacterEffectsProvider[]|CharacterEffectsProvidersCollection */
    public CharacterEffectsProvidersCollection $effectProviders;
    protected int $positionRow = 0;
    protected int $positionColumn = 0;
    /** @var callable[] */
    protected array $statuses = [];

    /**
     *
     * @param array $stats Stats of the character
     * @param Equipment[] $equipment Equipment of the character
     * @param Pet[] $pets Pets owned by the character
     * @param BaseCharacterSkill[] $skills Skills acquired by the character
     */
    public function __construct(
        array $stats,
        array $equipment = [],
        array $pets = [],
        array $skills = [],
        IInitiativeFormulaParser $initiativeFormulaParser = new InitiativeFormulaParser()
    ) {
        $this->initiativeFormulaParser = $initiativeFormulaParser;
        $this->equipment = EquipmentCollection::fromArray($equipment);
        $this->pets = PetsCollection::fromArray($pets);
        $this->effectProviders = CharacterEffectsProvidersCollection::fromArray(array_merge($equipment, $pets));
        $this->skills = CharacterSkillsCollection::fromArray($skills);
        $this->equipment->lock();
        $this->pets->lock();
        $this->skills->lock();
        $this->setStats($stats);
        $this->effects = new CharacterEffectsCollection($this);
        $this->registerDefaultStatuses();
    }

    protected function registerDefaultStatuses(): void
    {
        $this->registerStatus(
            static::STATUS_STUNNED,
            static fn(Character $character): bool => $character->effects->hasItems(["type" => SkillSpecial::TYPE_STUN])
        );
        $this->registerStatus(static::STATUS_POISONED, function (Character $character): int {
            $poisons = $character->effects->getItems(["type" => SkillSpecial::TYPE_POISON]);
            $poisonValue = 0;
            /** @var CharacterEffect $poison */
            foreach ($poisons as $poison) {
                $poisonValue += $poison->value;
            }
            return $poisonValue;
        });
        $this->registerStatus(
            static::STATUS_HIDDEN,
            static fn(Character $character): bool => $character->effects->hasItems(["type" => SkillSpecial::TYPE_HIDE])
        );
    }

    protected function setStats(array $stats): void
    {
        $requiredStats = array_merge(["id", "name", "level", "initiativeFormula",], static::BASE_STATS);
        $allStats = array_merge($requiredStats, ["occupation", "race", "specialization", "gender",]);
        $numberStats = static::BASE_STATS;
        $textStats = ["name", "race", "occupation", "specialization", "initiativeFormula",];
        $resolver = new OptionsResolver();
        $resolver->setDefined($allStats);
        $resolver->setAllowedTypes("id", ["integer", "string",]);
        foreach ($numberStats as $stat) {
            $resolver->setAllowedTypes($stat, ["integer", "float"]);
            $resolver->setNormalizer($stat, static fn(OptionsResolver $resolver, $value): int => (int) $value);
        }
        foreach ($textStats as $stat) {
            $resolver->setNormalizer($stat, static fn(OptionsResolver $resolver, $value): string => (string) $value);
        }
        $resolver->setRequired($requiredStats);
        $stats = array_filter($stats, static function ($key) use ($allStats): bool {
            return in_array($key, $allStats, true);
        }, ARRAY_FILTER_USE_KEY);
        $stats = $resolver->resolve($stats);
        foreach ($stats as $key => $value) {
            $this->$key = $value;
            if (in_array($key, $numberStats, true)) {
                $this->{$key . "Base"} = $value;
            }
        }
        $this->hitpoints = $this->maxHitpoints = $this->maxHitpointsBase = $this->constitution * static::HITPOINTS_PER_CONSTITUTION;
        $this->recalculateSecondaryStats();
        $this->hitBase = $this->hit;
        $this->dodgeBase = $this->dodge;
    }

    protected function getId(): int|string
    {
        return $this->id;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    protected function getGender(): string
    {
        return $this->gender;
    }

    protected function getRace(): string
    {
        return $this->race;
    }

    protected function getOccupation(): string
    {
        return $this->occupation;
    }

    protected function getLevel(): int
    {
        return $this->level;
    }

    protected function getStrength(): int
    {
        return $this->strength;
    }

    protected function getStrengthBase(): int
    {
        return $this->strengthBase;
    }

    protected function getDexterity(): int
    {
        return $this->dexterity;
    }

    protected function getDexterityBase(): int
    {
        return $this->dexterityBase;
    }

    protected function getConstitution(): int
    {
        return $this->constitution;
    }

    protected function getConstitutionBase(): int
    {
        return $this->constitutionBase;
    }

    protected function getCharisma(): int
    {
        return $this->charisma;
    }

    protected function getCharismaBase(): int
    {
        return $this->charismaBase;
    }

    protected function getMaxHitpoints(): int
    {
        return $this->maxHitpoints;
    }

    protected function getMaxHitpointsBase(): int
    {
        return $this->maxHitpointsBase;
    }

    protected function getHitpoints(): int
    {
        return $this->hitpoints;
    }

    protected function getDamage(): int
    {
        return $this->damage;
    }

    protected function getDamageBase(): int
    {
        return $this->damageBase;
    }

    protected function getHit(): int
    {
        return $this->hit;
    }

    protected function getHitBase(): int
    {
        return $this->hitBase;
    }

    protected function getDodge(): int
    {
        return $this->dodge;
    }

    protected function getDodgeBase(): int
    {
        return $this->dodgeBase;
    }

    protected function getInitiative(): int
    {
        return $this->initiative;
    }

    protected function getInitiativeBase(): int
    {
        return $this->initiativeBase;
    }

    protected function getInitiativeFormula(): string
    {
        return $this->initiativeFormula;
    }

    protected function getDefense(): int
    {
        return (int) $this->defense;
    }

    protected function getDefenseBase(): int
    {
        return (int) $this->defenseBase;
    }

    protected function getActivePet(): ?int
    {
        /** @var Pet|null $pet */
        $pet = $this->pets->getItem(["deployed" => true]);
        if ($pet === null) {
            return null;
        }
        return $pet->id;
    }

    protected function isStunned(): bool
    {
        return $this->hasStatus(static::STATUS_STUNNED);
    }

    protected function isPoisoned(): bool
    {
        return $this->hasStatus(static::STATUS_POISONED);
    }

    protected function isHidden(): bool
    {
        return $this->hasStatus(static::STATUS_HIDDEN);
    }

    protected function getSpecialization(): string
    {
        return $this->specialization;
    }

    protected function getIntelligence(): int
    {
        return $this->intelligence;
    }

    protected function getIntelligenceBase(): int
    {
        return $this->intelligenceBase;
    }

    protected function getInitiativeFormulaParser(): IInitiativeFormulaParser
    {
        return $this->initiativeFormulaParser;
    }

    protected function setInitiativeFormulaParser(IInitiativeFormulaParser $initiativeFormulaParser): void
    {
        $oldParser = $this->initiativeFormulaParser;
        $this->initiativeFormulaParser = $initiativeFormulaParser;
        if ($oldParser !== $initiativeFormulaParser) {
            $this->recalculateStats();
        }
    }

    protected function getPositionRow(): int
    {
        return $this->positionRow;
    }

    protected function setPositionRow(int $positionRow): void
    {
        $this->positionRow = Numbers::clamp($positionRow, 1, PHP_INT_MAX);
    }

    protected function getPositionColumn(): int
    {
        return $this->positionColumn;
    }

    protected function setPositionColumn(int $positionColumn): void
    {
        $this->positionColumn = Numbers::clamp($positionColumn, 1, PHP_INT_MAX);
    }

    /**
     * Register a new possible character status
     *
     * @param string $name
     * @param callable $callback Used to determine whether the status is active/what value does it have. Is called with Character instance as parameter
     */
    public function registerStatus(string $name, callable $callback): void
    {
        $this->statuses[$name] = $callback;
    }

    public function getStatus(string $name): mixed
    {
        if (!array_key_exists($name, $this->statuses)) {
            return null;
        }
        return (call_user_func($this->statuses[$name], $this));
    }

    public function hasStatus(string $status): bool
    {
        if (!array_key_exists($status, $this->statuses)) {
            return false;
        }
        return (bool) (call_user_func($this->statuses[$status], $this));
    }

    /**
     * @internal
     */
    public function applyEffectProviders(): void
    {
        foreach ($this->effectProviders as $item) {
            $effects = $item->getCombatEffects();
            array_walk($effects, function (CharacterEffect $effect): void {
                $this->effects->removeByFilter(["id" => $effect->id]);
                $this->effects[] = $effect;
            });
        }
    }

    /**
     * @return BaseCharacterSkill[]
     */
    protected function getUsableSkills(): array
    {
        return $this->skills->getItems(["usable" => true]);
    }

    /**
     * Harm the character
     */
    public function harm(int $amount): void
    {
        $this->hitpoints -= Numbers::clamp($amount, 0, $this->hitpoints);
    }

    /**
     * Heal the character
     */
    public function heal(int $amount): void
    {
        $this->hitpoints += Numbers::clamp($amount, 0, $this->maxHitpoints - $this->hitpoints);
    }

    /**
     * Determine which (primary) stat should be used to calculate damage
     */
    public function damageStat(): string
    {
        /** @var Weapon|null $item */
        $item = $this->equipment->getItem(["%class%" => Weapon::class, "worn" => true,]);
        if ($item === null) {
            return static::STAT_STRENGTH;
        }
        return $item->damageStat;
    }

    /**
     * Recalculate secondary stats from the the primary ones
     */
    public function recalculateSecondaryStats(): void
    {
        $stats = [
            static::STAT_DAMAGE => $this->damageStat(), static::STAT_HIT => static::STAT_DEXTERITY,
            static::STAT_DODGE => static::STAT_DEXTERITY, static::STAT_MAX_HITPOINTS => static::STAT_CONSTITUTION,
            static::STAT_INITIATIVE => "",
        ];
        foreach ($stats as $secondary => $primary) {
            $gain = $this->$secondary - $this->{$secondary . "Base"};
            if ($secondary === static::STAT_DAMAGE) {
                $base = (int) round($this->$primary / 2);
            } elseif ($secondary === static::STAT_MAX_HITPOINTS) {
                $base = $this->$primary * static::HITPOINTS_PER_CONSTITUTION;
            } elseif ($secondary === static::STAT_INITIATIVE) {
                $base = $this->initiativeFormulaParser->calculateInitiative($this);
            } else {
                $base = $this->$primary * 3;
            }
            $this->{$secondary . "Base"} = $base;
            $this->$secondary = $base + $gain;
        }
    }

    /**
     * Recalculates stats of the character (mostly used during combat)
     */
    public function recalculateStats(): void
    {
        $stats = array_merge(static::BASE_STATS, static::SECONDARY_STATS);
        $debuffs = [];
        foreach ($stats as $stat) {
            $$stat = $this->{$stat . "Base"};
            $debuffs[$stat] = 0;
        }
        $this->effects->removeByFilter(["duration<" => 1]);
        foreach ($this->effects as $effect) {
            $stat = $effect->stat;
            $type = $effect->type;
            $bonus_value = 0;
            if (!in_array($type, SkillSpecial::NO_STAT_TYPES, true)) {
                $bonus_value = ($effect->valueAbsolute) ? $effect->value : $$stat / 100 * $effect->value;
            }
            if ($type === SkillSpecial::TYPE_BUFF) {
                $$stat += $bonus_value;
            } elseif ($type === SkillSpecial::TYPE_DEBUFF) {
                $debuffs[$stat] += $bonus_value;
            }
            unset($stat, $type, $bonus_value);
        }
        foreach ($debuffs as $stat => $value) {
            $value = min($value, 80);
            $bonus_value = $$stat / 100 * $value;
            $$stat -= $bonus_value;
        }
        foreach ($stats as $stat) {
            $this->$stat = (int) round($$stat);
        }
        $this->recalculateSecondaryStats();
    }

    /**
     * Reset character's initiative
     */
    public function resetInitiative(): void
    {
        $this->initiative = $this->initiativeBase = 0;
    }

    public function canAct(): bool
    {
        return !$this->hasStatus(static::STATUS_STUNNED) && $this->hitpoints > 0;
    }

    public function canDefend(): bool
    {
        return !$this->hasStatus(static::STATUS_STUNNED);
    }
}
