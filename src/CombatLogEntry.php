<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Data structure for combat action
 *
 * @author Jakub Konečný
 */
final class CombatLogEntry
{
    /** @internal */
    public const ACTION_POISON = "poison";

    public readonly Character $character1;
    public readonly Character $character2;
    public readonly string $action;
    public readonly string $name;
    public readonly bool $result;
    public readonly int $amount;

    public function __construct(array $action)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $action = $resolver->resolve($action);
        $this->action = $action["action"];
        $this->result = $action["result"];
        $this->amount = $action["amount"];
        $this->character1 = clone $action["character1"];
        $this->character2 = clone $action["character2"];
        $this->name = $action["name"];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $requiredStats = ["action", "result", "character1", "character2",];
        $resolver->setDefined(["amount", "name",]);
        $resolver->setRequired($requiredStats);
        $resolver->setAllowedTypes("action", "string");
        $resolver->setAllowedTypes("result", "bool");
        $resolver->setAllowedTypes("amount", "integer");
        $resolver->setDefault("amount", 0);
        $resolver->setAllowedTypes("name", "string");
        $resolver->setDefault("name", "");
        $resolver->setAllowedTypes("character1", Character::class);
        $resolver->setAllowedTypes("character2", Character::class);
    }
}
