<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

interface CombatLogRender
{
    /**
     * @param array<string, mixed> $params
     */
    public function render(array $params): string;
}
