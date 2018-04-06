<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Numbers,
    Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Structure for single character
 * 
 * @author Jakub Konečný
 * @property-read int|string $id
 * @property-read string $name
 * @property-read string $gender
 * @property-read string $race
 * @property-read string $occupation
 * @property-read int $level
 * @property-read int $experience
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
 * @property-read Equipment[] $equipment
 * @property-read Pet[] $pets
 * @property-read BaseCharacterSkill[] $skills
 * @property-read int|NULL $activePet
 * @property-read CharacterEffect[] $effects
 * @property-read ICharacterEffectProvider[] $effectProviders
 * @property-read bool $stunned
 * @property-read BaseCharacterSkill[] $usableSkills
 * @property IInitiativeFormulaParser $initiativeFormulaParser
 */
class Character {
  use \Nette\SmartObject;
  
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
  protected $experience = 0;
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
  /** @var Equipment[] Character's equipment */
  protected $equipment = [];
  /** @var Pet[] Character's pets */
  protected $pets = [];
  /** @var BaseCharacterSkill[] Character's skills */
  protected $skills = [];
  /** @var int|NULL */
  protected $activePet = null;
  /** @var CharacterEffect[] Active effects */
  protected $effects = [];
  /** @var ICharacterEffectProvider[] */
  protected $effectProviders = [];
  /** @var bool */
  protected $stunned = false;
  
  /**
   * 
   * @param array $stats Stats of the character
   * @param Equipment[] $equipment Equipment of the character
   * @param Pet[] $pets Pets owned by the character
   * @param BaseCharacterSkill[] $skills Skills acquired by the character
   */
  public function __construct(array $stats, array $equipment = [], array $pets = [], array $skills = [], IInitiativeFormulaParser $initiativeFormulaParser = NULL) {
    $this->setStats($stats);
    foreach($equipment as $eq) {
      if($eq instanceof Equipment) {
        $this->equipment[] = $eq;
        $this->addEffectProvider($eq);
      }
    }
    foreach($pets as $pet) {
      if($pet instanceof Pet) {
        $this->pets[] = $pet;
        $this->addEffectProvider($pet);
      }
    }
    foreach($skills as $skill) {
      if($skill instanceof BaseCharacterSkill) {
        $this->skills[] = $skill;
      }
    }
    $this->initiativeFormulaParser = $initiativeFormulaParser ?? new InitiativeFormulaParser();
  }
  
  protected function setStats(array $stats): void {
    $requiredStats = ["id", "name", "level", "strength", "dexterity", "constitution", "intelligence", "charisma", "initiativeFormula",];
    $allStats = array_merge($requiredStats, ["occupation", "race", "specialization", "gender", "experience",]);
    $numberStats = ["strength", "dexterity", "constitution", "intelligence", "charisma",];
    $textStats = ["name", "race", "occupation", "initiativeFormula",];
    $resolver = new OptionsResolver();
    $resolver->setDefined($allStats);
    $resolver->setAllowedTypes("id", ["integer", "string"]);
    $resolver->setAllowedTypes("experience", "integer");
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
    $this->hitpoints = $this->maxHitpoints = $this->maxHitpointsBase = $this->constitution * 5;
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
  
  public function getExperience(): int {
    return $this->experience;
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
   * @return Equipment[]
   */
  public function getEquipment(): array {
    return $this->equipment;
  }
  
  /**
   * @return Pet[]
   */
  public function getPets(): array {
    return $this->pets;
  }
  
  /**
   * @return BaseCharacterSkill[]
   */
  public function getSkills(): array {
    return $this->skills;
  }
  
  public function getActivePet(): ?int {
    foreach($this->pets as $pet) {
      if($pet->deployed) {
        return $pet->id;
      }
    }
    return NULL;
  }
  
  /**
   * @return CharacterEffect[]
   */
  public function getEffects(): array {
    return $this->effects;
  }
  
  /**
   * @return ICharacterEffectProvider[]
   */
  public function getEffectProviders(): array {
    return $this->effectProviders;
  }
  
  public function isStunned(): bool {
    return $this->stunned;
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
    $this->initiativeFormulaParser = $initiativeFormulaParser;
  }
  
  /**
   * Applies new effect on the character
   */
  public function addEffect(CharacterEffect $effect): void {
    $this->effects[] = $effect;
    $this->recalculateStats();
  }
  
  public function addEffectProvider(ICharacterEffectProvider $provider): void {
    $this->effectProviders[] = $provider;
  }
  
  /**
   * Removes specified effect from the character
   *
   * @throws \OutOfBoundsException
   */
  public function removeEffect(string $effectId): void {
    foreach($this->effects as $i => $effect) {
      if($effect->id == $effectId) {
        unset($this->effects[$i]);
        $effect->onRemove($this, $effect);
        $this->recalculateStats();
        return;
      }
    }
    throw new \OutOfBoundsException("Effect to remove was not found.");
  }
  
  /**
   * Get specified equipment of the character
   *
   * @throws \OutOfBoundsException
   */
  public function getItem(int $itemId): Equipment {
    foreach($this->equipment as $equipment) {
      if($equipment->id === $itemId) {
        return $equipment;
      }
    }
    throw new \OutOfBoundsException("Item was not found.");
  }
  
  /**
   * Get specified pet
   *
   * @throws \OutOfBoundsException
   */
  public function getPet(int $petId): Pet {
    foreach($this->pets as $pet) {
      if($pet->id === $petId) {
        return $pet;
      }
    }
    throw new \OutOfBoundsException("Pet was not found.");
  }
  
  /**
   * @return BaseCharacterSkill[]
   */
  public function getUsableSkills(): array {
    return array_values(array_filter($this->skills, function(BaseCharacterSkill $skill) {
      return $skill->canUse();
    }));
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
    $stat = "strength";
    foreach($this->equipment as $item) {
      if(!$item->worn OR $item->slot != Equipment::SLOT_WEAPON) {
        continue;
      }
      switch($item->type) {
        case Equipment::TYPE_STAFF:
          $stat = "intelligence";
          break;
        case Equipment::TYPE_CLUB:
          $stat = "constitution";
          break;
        case Equipment::TYPE_BOW:
        case Equipment::TYPE_THROWING_KNIFE:
          $stat = "dexterity";
          break;
      }
    }
    return $stat;
  }
  
  /**
   * Recalculate secondary stats from the the primary ones
   */
  public function recalculateSecondaryStats(): void {
    $stats = [
      "damage" => $this->damageStat(), "hit" => "dexterity", "dodge" => "dexterity", "maxHitpoints" => "constitution",
    ];
    foreach($stats as $secondary => $primary) {
      $gain = $this->$secondary - $this->{$secondary . "Base"};
      if($secondary === "damage") {
        $base = (int) round($this->$primary / 2);
      } elseif($secondary === "maxHitpoints") {
        $base = $this->$primary * 5;
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
    $stats = [
      "strength", "dexterity", "constitution", "intelligence", "charisma",
      "damage", "hit", "dodge", "initiative", "defense", "maxHitpoints"
    ];
    $stunned = false;
    $debuffs = [];
    foreach($stats as $stat) {
      $$stat = $this->{$stat . "Base"};
      $debuffs[$stat] = 0;
    }
    foreach($this->effects as $i => $effect) {
      $stat = $effect->stat;
      $type = $effect->type;
      $duration = $effect->duration;
      if(is_int($duration) AND $duration < 0) {
        unset($this->effects[$i]);
        continue;
      }
      switch($effect->source) {
        case CharacterEffect::SOURCE_PET:
        case CharacterEffect::SOURCE_SKILL:
          if(!in_array($type, SkillSpecial::NO_STAT_TYPES, true)) {
            $bonus_value = $$stat / 100 * $effect->value;
          }
          break;
        case CharacterEffect::SOURCE_EQUIPMENT:
          if(!in_array($type, SkillSpecial::NO_STAT_TYPES, true)) {
            $bonus_value = $effect->value;
          }
          break;
      }
      if($type == SkillSpecial::TYPE_BUFF) {
        $$stat += $bonus_value;
      } elseif($type == SkillSpecial::TYPE_DEBUFF) {
        $debuffs[$stat] += $bonus_value;
      } elseif($type == SkillSpecial::TYPE_STUN) {
        $stunned = true;
      }
      unset($stat, $type, $duration, $bonus_value);
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
    $this->stunned = $stunned;
  }
  
  /**
   * Calculate character's initiative
   */
  public function calculateInitiative(): void {
    $this->initiative = $this->initiativeFormulaParser->calculateInitiative($this->initiativeFormula, $this);
  }
  
  /**
   * Reset character's initiative
   */
  public function resetInitiative(): void {
    $this->initiative = $this->initiativeBase;
  }
}
?>