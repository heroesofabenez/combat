<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\Character;

interface ICombatAction {
  public function getName(): string;
  public function getPriority(): int;
  public function shouldUse(CombatBase $combat, Character $character): bool;
  public function do(CombatBase $combat, Character $character): void;
}
?>