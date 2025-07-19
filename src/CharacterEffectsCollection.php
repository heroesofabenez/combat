<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Utils\Arrays;

/**
 * @author Jakub Konečný
 * @internal
 */
final class CharacterEffectsCollection extends \Nexendrie\Utils\Collection {
  protected string $class = CharacterEffect::class;

  public function __construct(private Character $character) {
    parent::__construct();
  }

  /**
   * @param int|NULL $index
   * @param CharacterEffect $item
   * @throws \OutOfRangeException
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function offsetSet($index, $item): void {
    parent::offsetSet($index, $item);
    $item->onApply($this->character, $item);
  }

  /**
   * @param int $index
   * @throws \RuntimeException
   * @throws \OutOfRangeException
   */
  public function offsetUnset($index): void {
    try {
      /** @var CharacterEffect $item */
      $item = Arrays::get($this->items, $index);
    } catch(\Nette\InvalidArgumentException $e) {
      throw new \OutOfRangeException("Offset invalid or out of range.");
    }
    parent::offsetUnset($index);
    $item->onRemove($this->character, $item);
  }
}
?>