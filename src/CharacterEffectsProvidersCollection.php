<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * @author Jakub Konečný
 * @internal
 */
class CharacterEffectsProvidersCollection extends \Nexendrie\Utils\Collection {
  protected string $class = ICharacterEffectsProvider::class;
}
?>