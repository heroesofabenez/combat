<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Collection;
use Nexendrie\Utils\Numbers;

/**
 * Structure for a team in combat
 * 
 * @author Jakub Konečný
 * @property-read string $name
 * @property-read Character[] $aliveMembers
 * @property-read Character[] $usableMembers
 * @property int $maxRowSize
 * @property-read int|null $rowToAttack
 */
final class Team extends Collection {
  protected const LOWEST_HP_THRESHOLD = 0.5;

  /** @var string */
  protected $class = Character::class;
  /** @var string Name of the team */
  protected $name;
  /** @var int */
  protected $maxRowSize = 5;
  
  use \Nette\SmartObject;
  
  public function __construct(string $name) {
    parent::__construct();
    $this->name = $name;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  public function getMaxRowSize(): int {
    return $this->maxRowSize;
  }
  
  public function setMaxRowSize(int $maxRowSize): void {
    $this->maxRowSize = Numbers::range($maxRowSize, 1, PHP_INT_MAX);
  }
  
  /**
   * Get alive members from the team
   * 
   * @return Character[]
   */
  public function getAliveMembers(): array {
    return $this->getItems(["hitpoints>" => 0, ]);
  }
  
  /**
   * Get members which can perform an action
   * 
   * @return Character[]
   */
  public function getUsableMembers(): array {
    return $this->getItems([
      "stunned" => false, "hitpoints>" => 0,
    ]);
  }
  
  /**
   * Check whether the team has at least 1 alive member
   */
  public function hasAliveMembers(): bool {
    return count($this->getAliveMembers()) > 0;
  }
  
  /**
   * Set character's position in team
   *
   * @param string|int $id
   * @throws \OutOfBoundsException
   * @throws InvalidCharacterPositionException
   */
  public function setCharacterPosition($id, int $row, int $column): void {
    if(!$this->hasItems(["id" => $id])) {
      throw new \OutOfBoundsException("Character $id is not in the team");
    } elseif(count($this->getItems(["positionRow" => $row])) >= $this->maxRowSize) {
      throw new InvalidCharacterPositionException("Row $row is full.", InvalidCharacterPositionException::ROW_FULL);
    } elseif($this->hasItems(["positionRow" => $row, "positionColumn" => $column])) {
      throw new InvalidCharacterPositionException("Row $row column $column is occupied.", InvalidCharacterPositionException::POSITION_OCCUPIED);
    }
    $character = $this->getItems(["id" => $id])[0];
    $character->positionRow = $row;
    $character->positionColumn = $column;
  }
  
  public function getRandomCharacter(): ?Character {
    $characters = $this->aliveMembers;
    if(count($characters) === 0) {
      return null;
    } elseif(count($characters) === 1) {
      return $characters[0];
    }
    $roll = rand(0, count($characters) - 1);
    return $characters[$roll];
  }
  
  public function getLowestHpCharacter(float $threshold = self::LOWEST_HP_THRESHOLD): ?Character {
    $lowestHp = PHP_INT_MAX;
    $lowestIndex = PHP_INT_MIN;
    if(count($this->aliveMembers) === 0) {
      return null;
    }
    foreach($this->aliveMembers as $index => $member) {
      if($member->hitpoints <= $member->maxHitpoints * $threshold && $member->hitpoints < $lowestHp) {
        $lowestHp = $member->hitpoints;
        $lowestIndex = $index;
      }
    }
    if($lowestIndex === PHP_INT_MIN) {
      return null;
    }
    return $this->aliveMembers[$lowestIndex];
  }
  
  public function getRowToAttack(): ?int {
    if(count($this->aliveMembers) === 0) {
      return null;
    }
    for($i = 1; $i <= PHP_INT_MAX; $i++) {
      if($this->hasItems(["positionRow" => $i, "hitpoints>" => 0, ])) {
        return $i;
      }
    }
  }
}
?>