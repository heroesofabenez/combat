<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Data structure for combat action
 *
 * @author Jakub Konečný
 */
final readonly class CombatLogEntry
{
    /** @internal */
    public const string ACTION_POISON = "poison";

    public Character $character1;
    public Character $character2;
    public string $action;
    public string $name;
    public bool $result;
    public int $amount;

    public function __construct(array $action)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $action = $resolver->resolve($action);
        $this->action = $action["action"];
        $this->result = $action["result"];
        $this->amount = $action["amount"];
        $this->character1 = clone $action["character1"]; // @phpstan-ignore assign.propertyType
        $this->character2 = clone $action["character2"]; // @phpstan-ignore assign.propertyType
        $this->name = $action["name"];
    }

    private function configureOptions(OptionsResolver $resolver): void
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
