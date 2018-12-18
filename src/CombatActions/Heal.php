<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogEntry;
use Nexendrie\Utils\Numbers;

final class Heal implements ICombatAction {
  use \Nette\SmartObject;

  public function getName(): string {
    return CombatLogEntry::ACTION_HEALING;
  }

  public function getPriority(): int {
    return 1000;
  }

  public function shouldUse(CombatBase $combat, Character $character): bool {
    return (in_array($character, $combat->findHealers()->toArray(), true) AND !is_null($combat->selectHealingTarget($character)));
  }

  public function do(CombatBase $combat, Character $character): void {
    $result = [];
    /** @var Character $patient */
    $patient = $combat->selectHealingTarget($character);
    $result["result"] = $combat->successCalculator->hasHealed($character);
    $amount = ($result["result"]) ? (int) ($character->intelligence / 2) : 0;
    $result["amount"] = Numbers::range($amount, 0, $patient->maxHitpoints - $patient->hitpoints);
    if($result["amount"] > 0) {
      $patient->heal($result["amount"]);
    }
    $result["action"] = $this->getName();
    $result["name"] = "";
    $result["character1"] = $character;
    $result["character2"] = $patient;
    $combat->log->log($result);
  }
}
?>