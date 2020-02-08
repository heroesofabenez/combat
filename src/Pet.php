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
final class Pet implements ICharacterEffectsProvider {
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
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $data = $resolver->resolve($data);
    $this->id = $data["id"];
    $this->deployed = $data["deployed"];
    $this->bonusStat = $data["bonusStat"];
    $this->bonusValue = $data["bonusValue"];
  }

  protected function configureOptions(OptionsResolver $resolver): void {
    $allStats = ["id", "deployed", "bonusStat", "bonusValue", ];
    $resolver->setRequired($allStats);
    $resolver->setAllowedTypes("id", "integer");
    $resolver->setAllowedTypes("deployed", "boolean");
    $resolver->setAllowedTypes("bonusStat", "string");
    $resolver->setAllowedValues("bonusStat", function(string $value): bool {
      return in_array($value, $this->getAllowedStats(), true);
    });
    $resolver->setAllowedTypes("bonusValue", "integer");
    $resolver->setAllowedValues("bonusValue", function(int $value): bool {
      return ($value >= 0);
    });
  }
  
  protected function getAllowedStats(): array {
    return Character::BASE_STATS;
  }
  
  protected function getId(): int {
    return $this->id;
  }
  
  protected function isDeployed(): bool {
    return $this->deployed;
  }
  
  protected function setDeployed(bool $deployed): void {
    $this->deployed = $deployed;
  }
  
  protected function getBonusStat(): string {
    return $this->bonusStat;
  }
  
  protected function getBonusValue(): int {
    return $this->bonusValue;
  }
  
  protected function getDeployParams(): array {
    return [
      "id" => "pet" . $this->id . "bonusEffect",
      "type" => SkillSpecial::TYPE_BUFF,
      "stat" => $this->bonusStat,
      "value" => $this->bonusValue,
      "valueAbsolute" => false,
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