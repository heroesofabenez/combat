<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Collection,
    Nexendrie\Utils\Numbers;

/**
 * Structure for a team in combat
 * 
 * @author Jakub Konečný
 * @property-read string $name
 * @property-read Character[] $aliveMembers
 * @property-read Character[] $usableMembers
 * @property int $maxRowSize
 */
final class Team extends Collection {
  protected $class = Character::class;
  /** @var string Name of the team */
  protected $name;
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
   * Check if the team has at least 1 member matching the filter
   */
  public function hasMembers(array $filter = []): bool {
    return (count($this->getMembers($filter)) > 0);
  }
  
  /**
   * Get all team members matching the filter
   *
   * @todo make it possible to use different comparing rules
   * @return Character[]
   */
  public function getMembers(array $filter = []): array {
    if(count($filter) === 0) {
      return $this->items;
    }
    return array_values(array_filter($this->items, function(Character $character) use($filter) {
      foreach($filter as $key => $value) {
        if($character->$key !== $value) {
          return false;
        }
      }
      return true;
    }));
  }
  
  /**
   * Get alive members from the team
   * 
   * @return Character[]
   */
  public function getAliveMembers(): array {
    return array_values(array_filter($this->items, function(Character $value) {
      return ($value->hitpoints > 0);
    }));
  }
  
  /**
   * Get members which can perform an action
   * 
   * @return Character[]
   */
  public function getUsableMembers(): array {
    return array_values(array_filter($this->items, function(Character $value) {
      return (!$value->stunned AND $value->hitpoints > 0);
    }));
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
    if(!$this->hasMembers(["id" => $id])) {
      throw new \OutOfBoundsException("Character $id is not in the team");
    } elseif(count($this->getMembers(["positionRow" => $row])) >= $this->maxRowSize) {
      throw new InvalidCharacterPositionException("Row $row is full.", InvalidCharacterPositionException::ROW_FULL);
    } elseif($this->hasMembers(["positionRow" => $row, "positionColumn" => $column])) {
      throw new InvalidCharacterPositionException("Row $row column $column is occupied.", InvalidCharacterPositionException::POSITION_OCCUPIED);
    }
    $character = $this->getMembers(["id" => $id])[0];
    $character->positionRow = $row;
    $character->positionColumn = $column;
  }
}
?>