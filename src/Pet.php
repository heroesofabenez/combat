<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Pet
 *
 * @author Jakub Konečný
 * @property-read int $id
 * @property bool $deployed
 * @property-read string $bonusStat
 * @property-read int $bonusValue
 */
class Pet implements ICharacterEffectsProvider {
  use \Nette\SmartObject;
  
  /** @var int */
  protected $id;
  /** @var bool */
  protected $deployed;
  /** @var string */
  protected $bonusStat;
  /** @var int */
  protected $bonusValue;
  
  public function __construct(array $data) {
    $allStats = ["id", "deployed", "bonusStat", "bonusValue",];
    $resolver = new OptionsResolver();
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("id", "integer");
    $resolver->setAllowedTypes("deployed", "boolean");
    $resolver->setAllowedTypes("bonusStat", "string");
    $resolver->setAllowedValues("bonusStat", function(string $value) {
      return in_array($value, $this->getAllowedStats(), true);
    });
    $resolver->setAllowedTypes("bonusValue", "integer");
    $resolver->setAllowedValues("bonusValue", function(int $value) {
      return ($value >= 0);
    });
    $data = $resolver->resolve($data);
    $this->id = $data["id"];
    $this->deployed = $data["deployed"];
    $this->bonusStat = $data["bonusStat"];
    $this->bonusValue = $data["bonusValue"];
  }
  
  protected function getAllowedStats(): array {
    return Character::BASE_STATS;
  }
  
  public function getId(): int {
    return $this->id;
  }
  
  public function isDeployed(): bool {
    return $this->deployed;
  }
  
  public function setDeployed(bool $deployed): void {
    $this->deployed = $deployed;
  }
  
  public function getBonusStat(): string {
    return $this->bonusStat;
  }
  
  public function getBonusValue(): int {
    return $this->bonusValue;
  }
  
  protected function getDeployParams(): array {
    return [
      "id" => "pet" . $this->id . "bonusEffect",
      "type" => SkillSpecial::TYPE_BUFF,
      "stat" => $this->bonusStat,
      "value" => $this->bonusValue,
      "source" => CharacterEffect::SOURCE_PET,
      "duration" => CharacterEffect::DURATION_COMBAT,
    ];
  }
  
  public function getCombatEffects(): array {
    if(!$this->deployed) {
      return [];
    }
    return [new CharacterEffect($this->getDeployParams())];
  }
}
?>