<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

interface CombatLogRender
{
    public function render(array $params): string;
}
