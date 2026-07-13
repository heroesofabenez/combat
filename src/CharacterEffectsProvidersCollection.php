<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * @author Jakub Konečný
 * @internal
 * @extends \Nexendrie\Utils\Collection<CharacterEffectsProvider>
 */
class CharacterEffectsProvidersCollection extends \Nexendrie\Utils\Collection
{
    public function __construct()
    {
        parent::__construct();
        $this->class = CharacterEffectsProvider::class;
    }
}
