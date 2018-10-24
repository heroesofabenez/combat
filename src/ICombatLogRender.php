<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

interface ICombatLogRender {
  public function render(array $params): string;
}
?>