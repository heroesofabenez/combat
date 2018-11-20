<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use HeroesofAbenez\Combat\CombatLogEntry;
use Nexendrie\Utils\Numbers;

final class Attack implements ICombatAction {
  use \Nette\SmartObject;

  public function getName(): string {
    return CombatLogEntry::ACTION_ATTACK;
  }

  public function getPriority(): int {
    return 0;
  }

  public function shouldUse(CombatBase $combat, Character $character): bool {
    return true;
  }

  /**
   * Do an attack
   * Hit chance = Attacker's hit - Defender's dodge, but at least 15%
   * Damage = Attacker's damage - defender's defense
   */
  public function do(CombatBase $combat, Character $character): void {
    $result = [];
    /** @var Character $defender */
    $defender = $combat->selectAttackTarget($character);
    $result["result"] = $combat->successCalculator->hasHit($character, $defender);
    $result["amount"] = 0;
    if($result["result"]) {
      $amount = $character->damage - $defender->defense;
      $result["amount"] = Numbers::range($amount, 0, $defender->hitpoints);
    }
    if($result["amount"] > 0) {
      $defender->harm($result["amount"]);
    }
    $result["action"] = CombatLogEntry::ACTION_ATTACK;
    $result["name"] = "";
    $result["character1"] = $character;
    $result["character2"] = $defender;
    $combat->logDamage($character, $result["amount"]);
    $combat->log->log($result);
  }
}
?>