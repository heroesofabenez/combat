<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nexendrie\Utils\Collection,
    Nette\Utils\Arrays;

/**
 * Structure for a team in combat
 * 
 * @author Jakub Konečný
 * @property-read string $name
 * @property-read Character[] $aliveMembers
 * @property-read Character[] $usableMembers
 */
final class Team extends Collection {
  protected $class = Character::class;
  /** @var string Name of the team */
  protected $name;
  
  use \Nette\SmartObject;
  
  public function __construct(string $name) {
    parent::__construct();
    $this->name = $name;
  }
  
  public function getName(): string {
    return $this->name;
  }
  
  /**
   * Check if the team has at least 1 member matching the filter
   *
   * @todo make it possible to use different comparing rules
   */
  public function hasMembers(array $filter = []): bool {
    if(count($filter) === 0) {
      return (count($this->items) > 0);
    }
    return Arrays::some($this->items, function(Character $character) use($filter) {
      foreach($filter as $key => $value) {
        if($character->$key !== $value) {
          return false;
        }
      }
      return true;
    });
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
}
?>