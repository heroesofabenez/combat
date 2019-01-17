<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nexendrie\Utils\Collection;

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
 * @property-read Equipment[]|Collection $equipment
 * @property-read Pet[]|Collection $pets
 * @property-read BaseCharacterSkill[]|Collection $skills
 * @property-read int|null $activePet
 * @property CharacterEffect[]|CharacterEffectsCollection $effects
 * @property ICharacterEffectsProvider[]|Collection $effectProviders
 * @property-read bool $stunned
 * @property-read bool $poisoned
 * @property-read BaseCharacterSkill[] $usableSkills
 * @property IInitiativeFormulaParser $initiativeFormulaParser
 * @property int $positionRow
 * @property int $positionColumn
 */
class Character {
  use \Nette\SmartObject;
  
  public const HITPOINTS_PER_CONSTITUTION = 5;
  public const STAT_STRENGTH = "strength";
  public const STAT_DEXTERITY = "dexterity";
  public const STAT_CONSTITUTION = "constitution";
  public const STAT_INTELLIGENCE = "intelligence";
  public const STAT_CHARISMA = "charisma";
  public const STAT_MAX_HITPOINTS = "maxHitpoints";
  public const STAT_DAMAGE = "damage";
  public const STAT_DEFENSE = "defense";
  public const STAT_HIT = "hit";
  public const STAT_DODGE = "dodge";
  public const STAT_INITIATIVE = "initiative";
  public const BASE_STATS = [
    self::STAT_STRENGTH, self::STAT_DEXTERITY, self::STAT_CONSTITUTION, self::STAT_INTELLIGENCE, self::STAT_CHARISMA,
  ];
  public const SECONDARY_STATS = [
    self::STAT_MAX_HITPOINTS, self::STAT_DAMAGE, self::STAT_DEFENSE, self::STAT_HIT, self::STAT_DODGE, self::STAT_INITIATIVE,
  ];
  public const STATUS_STUNNED = "stunned";
  public const STATUS_POISONED = "poisoned";
  
  /** @var int|string */
  protected $id;
  /** @var string */
  protected $name;
  /** @var string */
  protected $gender = "male";
  /** @var string */
  protected $race;
  /** @var string */
  protected $occupation;
  /** @var string */
  protected $specialization;
  /** @var int */
  protected $level;
  /** @var int */
  protected $strength;
  /** @var int */
  protected $strengthBase;
  /** @var int */
  protected $dexterity;
  /** @var int */
  protected $dexterityBase;
  /** @var int */
  protected $constitution;
  /** @var int */
  protected $constitutionBase;
  /** @var int */
  protected $intelligence;
  /** @var int */
  protected $intelligenceBase;
  /** @var int */
  protected $charisma;
  /** @var int */
  protected $charismaBase;
  /** @var int */
  protected $maxHitpoints;
  /** @var int */
  protected $maxHitpointsBase;
  /** @var int */
  protected $hitpoints;
  /** @var int */
  protected $damage = 0;
  /** @var int */
  protected $damageBase = 0;
  /** @var int */
  protected $hit = 0;
  /** @var int */
  protected $hitBase = 0;
  /** @var int */
  protected $dodge = 0;
  /** @var int */
  protected $dodgeBase = 0;
  /** @var int */
  protected $initiative = 0;
  /** @var int */
  protected $initiativeBase = 0;
  /** @var string */
  protected $initiativeFormula;
  /** @var IInitiativeFormulaParser */
  protected $initiativeFormulaParser;
  /** @var float */
  protected $defense = 0;
  /** @var float */
  protected $defenseBase = 0;
  /** @var Equipment[]|Collection Character's equipment */
  protected $equipment;
  /** @var Pet[]|Collection Character's pets */
  protected $pets;
  /** @var BaseCharacterSkill[]|Collection Character's skills */
  protected $skills;
  /** @var int|null */
  protected $activePet = null;
  /** @var CharacterEffect[]|CharacterEffectsCollection Active effects */
  protected $effects;
  /** @var ICharacterEffectsProvider[]|Collection */
  protected $effectProviders;
  /** @var int */
  protected $positionRow = 0;
  /** @var int */
  protected $positionColumn = 0;
  /** @var callable[] */
  protected $statuses = [];
  
  /**
   *
   * @param array $stats Stats of the character
   * @param Equipment[] $equipment Equipment of the character
   * @param Pet[] $pets Pets owned by the character
   * @param BaseCharacterSkill[] $skills Skills acquired by the character
   */
  public function __construct(array $stats, array $equipment = [], array $pets = [], array $skills = [], IInitiativeFormulaParser $initiativeFormulaParser = null) {
    $this->initiativeFormulaParser = $initiativeFormulaParser ?? new InitiativeFormulaParser();
    $this->effectProviders = new class extends  Collection {
      /** @var string */
      protected $class = ICharacterEffectsProvider::class;
    };
    $this->equipment = new class extends Collection {
      /** @var string */
      protected $class = Equipment::class;
    };
    foreach($equipment as $eq) {
      $this->equipment[] = $this->effectProviders[] = $eq;
    }
    $this->pets = new class extends Collection {
      /** @var string */
      protected $class = Pet::class;
    };
    foreach($pets as $pet) {
      $this->pets[] = $this->effectProviders[] = $pet;
    }
    $this->skills = new class extends Collection {
      /** @var string */
      protected $class = BaseCharacterSkill::class;
    };
    foreach($skills as $skill) {
      $this->skills[] = $skill;
    }
    $this->equipment->lock();
    $this->pets->lock();
    $this->skills->lock();
    $this->setStats($stats);
    $this->effects = new CharacterEffectsCollection($this);
    $this->registerDefaultStatuses();
  }

  protected function registerDefaultStatuses(): void {
    $this->registerStatus(static::STATUS_STUNNED, function(Character $character) {
      return $character->effects->hasItems(["type" => SkillSpecial::TYPE_STUN]);
    });
    $this->registerStatus(static::STATUS_POISONED, function(Character $character) {
      $poisons = $character->effects->getItems(["type" => SkillSpecial::TYPE_POISON]);
      $poisonValue = 0;
      /** @var CharacterEffect $poison */
      foreach($poisons as $poison) {
        $poisonValue += $poison->value;
      }
      return $poisonValue;
    });
  }
  
  protected function setStats(array $stats): void {
    $requiredStats = array_merge(["id", "name", "level", "initiativeFormula",], static::BASE_STATS);
    $allStats = array_merge($requiredStats, ["occupation", "race", "specialization", "gender",]);
    $numberStats = static::BASE_STATS;
    $textStats = ["name", "race", "occupation", "specialization", "initiativeFormula",];
    $resolver = new OptionsResolver();
    $resolver->setDefined($allStats);
    $resolver->setAllowedTypes("id", ["integer", "string"]);
    foreach($numberStats as $stat) {
      $resolver->setAllowedTypes($stat, ["integer", "float"]);
      $resolver->setNormalizer($stat, function(OptionsResolver $resolver, $value) {
        return (int) $value;
      });
    }
    foreach($textStats as $stat) {
      $resolver->setNormalizer($stat, function(OptionsResolver $resolver, $value) {
        return (string) $value;
      });
    }
    $resolver->setRequired($requiredStats);
    $stats = array_filter($stats, function($key) use($allStats) {
      return in_array($key, $allStats, true);
    }, ARRAY_FILTER_USE_KEY);
    $stats = $resolver->resolve($stats);
    foreach($stats as $key => $value) {
      if(in_array($key, $numberStats, true)) {
        $this->$key = $value;
        $this->{$key . "Base"} = $value;
      } else {
        $this->$key = $value;
      }
    }
    $this->hitpoints = $this->maxHitpoints = $this->maxHitpointsBase = $this->constitution * static::HITPOINTS_PER_CONSTITUTION;
    $this->recalculateSecondaryStats();
    $this->hitBase = $this->hit;
    $this->dodgeBase = $this->dodge;
  }
  
  /**
   * @return int|string
   */
  public function getId() {
    return $this->id;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  public function getGender(): string {
    return $this->gender;
  }
  
  public function getRace(): string {
    return $this->race;
  }
  
  public function getOccupation(): string {
    return $this->occupation;
  }
  
  public function getLevel(): int {
    return $this->level;
  }
  
  public function getStrength(): int {
    return $this->strength;
  }
  
  public function getStrengthBase(): int {
    return $this->strengthBase;
  }
  
  public function getDexterity(): int {
    return $this->dexterity;
  }
  
  public function getDexterityBase(): int {
    return $this->dexterityBase;
  }
  
  public function getConstitution(): int {
    return $this->constitution;
  }
  
  public function getConstitutionBase(): int {
    return $this->constitutionBase;
  }
  
  public function getCharisma(): int {
    return $this->charisma;
  }
  
  public function getCharismaBase(): int {
    return $this->charismaBase;
  }
  
  public function getMaxHitpoints(): int {
    return $this->maxHitpoints;
  }
  
  public function getMaxHitpointsBase(): int {
    return $this->maxHitpointsBase;
  }
  
  public function getHitpoints(): int {
    return $this->hitpoints;
  }
  
  public function getDamage(): int {
    return $this->damage;
  }
  
  public function getDamageBase(): int {
    return $this->damageBase;
  }
  
  public function getHit(): int {
    return $this->hit;
  }
  
  public function getHitBase(): int {
    return $this->hitBase;
  }
  
  public function getDodge(): int {
    return $this->dodge;
  }
  
  public function getDodgeBase(): int {
    return $this->dodgeBase;
  }
  
  public function getInitiative(): int {
    return $this->initiative;
  }
  
  public function getInitiativeBase(): int {
    return $this->initiativeBase;
  }
  
  public function getInitiativeFormula(): string {
    return $this->initiativeFormula;
  }
  
  public function getDefense(): int {
    return (int) $this->defense;
  }
  
  public function getDefenseBase(): int {
    return (int) $this->defenseBase;
  }
  
  /**
   * @return Equipment[]|Collection
   */
  public function getEquipment(): Collection {
    return $this->equipment;
  }
  
  /**
   * @return Pet[]|Collection
   */
  public function getPets(): Collection {
    return $this->pets;
  }
  
  /**
   * @return BaseCharacterSkill[]|Collection
   */
  public function getSkills(): Collection {
    return $this->skills;
  }

  public function getActivePet(): ?int {
    /** @var Pet|null $pet */
    $pet = $this->pets->getItem(["deployed" => true]);
    if(is_null($pet)) {
      return null;
    }
    return $pet->id;
  }

  public function getEffects(): CharacterEffectsCollection {
    return $this->effects;
  }
  
  /**
   * @return ICharacterEffectsProvider[]|Collection
   */
  public function getEffectProviders(): Collection {
    return $this->effectProviders;
  }
  
  public function isStunned(): bool {
    return $this->hasStatus(static::STATUS_STUNNED);
  }

  public function isPoisoned(): bool {
    return $this->hasStatus(static::STATUS_POISONED);
  }
  
  public function getSpecialization(): string {
    return $this->specialization;
  }
  
  public function getIntelligence(): int {
    return $this->intelligence;
  }
  
  public function getIntelligenceBase(): int {
    return $this->intelligenceBase;
  }
  
  public function getInitiativeFormulaParser(): IInitiativeFormulaParser {
    return $this->initiativeFormulaParser;
  }
  
  public function setInitiativeFormulaParser(IInitiativeFormulaParser $initiativeFormulaParser): void {
    $oldParser = $this->initiativeFormulaParser;
    $this->initiativeFormulaParser = $initiativeFormulaParser;
    if($oldParser !== $initiativeFormulaParser) {
      $this->recalculateStats();
    }
  }
  
  public function getPositionRow(): int {
    return $this->positionRow;
  }
  
  public function setPositionRow(int $positionRow): void {
    $this->positionRow = Numbers::range($positionRow, 1, PHP_INT_MAX);
  }
  
  public function getPositionColumn(): int {
    return $this->positionColumn;
  }
  
  public function setPositionColumn(int $positionColumn): void {
    $this->positionColumn = Numbers::range($positionColumn, 1, PHP_INT_MAX);
  }

  /**
   * Register a new possible character status
   *
   * @param string $name
   * @param callable $callback Used to determine whether the status is active/what value does it have. Is called with Character instance as parameter
   */
  public function registerStatus(string $name, callable $callback): void {
    $this->statuses[$name] = $callback;
  }

  /**
   * @return mixed
   */
  public function getStatus(string $name) {
    if(!array_key_exists($name, $this->statuses)) {
      return null;
    }
    return (call_user_func($this->statuses[$name], $this));
  }

  public function hasStatus(string $status): bool {
    if(!array_key_exists($status, $this->statuses)) {
      return false;
    }
    return (bool) (call_user_func($this->statuses[$status], $this));
  }
  
  /**
   * @internal
   */
  public function applyEffectProviders(): void {
    foreach($this->effectProviders as $item) {
      $effects = $item->getCombatEffects();
      array_walk($effects, function(CharacterEffect $effect) {
        $this->effects->removeByFilter(["id" => $effect->id]);
        $this->effects[] = $effect;
      });
    }
  }
  
  /**
   * @return BaseCharacterSkill[]
   */
  public function getUsableSkills(): array {
    return $this->skills->getItems(["usable" => true]);
  }
  
  /**
   * Harm the character
   */
  public function harm(int $amount): void {
    $this->hitpoints -= Numbers::range($amount, 0, $this->hitpoints);
  }
  
  /**
   * Heal the character
   */
  public function heal(int $amount): void {
    $this->hitpoints += Numbers::range($amount, 0, $this->maxHitpoints - $this->hitpoints);
  }
  
  /**
   * Determine which (primary) stat should be used to calculate damage
   */
  public function damageStat(): string {
    /** @var Weapon|null $item */
    $item = $this->equipment->getItem(["%class%" => Weapon::class, "worn" => true, ]);
    if(is_null($item)) {
      return static::STAT_STRENGTH;
    }
    return $item->damageStat;
  }
  
  /**
   * Recalculate secondary stats from the the primary ones
   */
  public function recalculateSecondaryStats(): void {
    $stats = [
      static::STAT_DAMAGE => $this->damageStat(), static::STAT_HIT => static::STAT_DEXTERITY,
      static::STAT_DODGE => static::STAT_DEXTERITY, static::STAT_MAX_HITPOINTS => static::STAT_CONSTITUTION,
      static::STAT_INITIATIVE => "",
    ];
    foreach($stats as $secondary => $primary) {
      $gain = $this->$secondary - $this->{$secondary . "Base"};
      if($secondary === static::STAT_DAMAGE) {
        $base = (int) round($this->$primary / 2);
      } elseif($secondary === static::STAT_MAX_HITPOINTS) {
        $base = $this->$primary * static::HITPOINTS_PER_CONSTITUTION;
      } elseif($secondary === static::STAT_INITIATIVE) {
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
  public function recalculateStats(): void {
    $stats = array_merge(static::BASE_STATS, static::SECONDARY_STATS);
    $debuffs = [];
    foreach($stats as $stat) {
      $$stat = $this->{$stat . "Base"};
      $debuffs[$stat] = 0;
    }
    $this->effects->removeByFilter(["duration<" => 1]);
    foreach($this->effects as $effect) {
      $stat = $effect->stat;
      $type = $effect->type;
      if(!in_array($type, SkillSpecial::NO_STAT_TYPES, true)) {
        $bonus_value = ($effect->valueAbsolute) ? $effect->value : $$stat / 100 * $effect->value;
      }
      if($type == SkillSpecial::TYPE_BUFF) {
        $$stat += $bonus_value;
      } elseif($type == SkillSpecial::TYPE_DEBUFF) {
        $debuffs[$stat] += $bonus_value;
      }
      unset($stat, $type, $bonus_value);
    }
    foreach($debuffs as $stat => $value) {
      $value = min($value, 80);
      $bonus_value = $$stat / 100 * $value;
      $$stat -= $bonus_value;
    }
    foreach($stats as $stat) {
      $this->$stat = (int) round($$stat);
    }
    $this->recalculateSecondaryStats();
  }
  
  /**
   * Reset character's initiative
   */
  public function resetInitiative(): void {
    $this->initiative = $this->initiativeBase = 0;
  }
}
?>