<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * @author Jakub Konečný
 * @internal
 */
final class CharacterEffectsCollection extends \Nexendrie\Utils\Collection
{
    protected string $class = CharacterEffect::class;

    public function __construct(private readonly Character $character)
    {
        parent::__construct();
    }

    /**
     * @param int|NULL $index
     * @param CharacterEffect $item
     * @throws \OutOfRangeException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function offsetSet($index, $item): void
    {
        parent::offsetSet($index, $item);
        $item->onApply($this->character, $item);
    }

    /**
     * @param int $index
     * @throws \RuntimeException
     * @throws \OutOfRangeException
     */
    public function offsetUnset($index): void
    {
        if (!array_key_exists($index, $this->items)) {
            throw new \OutOfRangeException("Offset invalid or out of range.");
        }
        /** @var CharacterEffect $item */
        $item = $this->items[$index];
        parent::offsetUnset($index);
        $item->onRemove($this->character, $item);
    }
}
