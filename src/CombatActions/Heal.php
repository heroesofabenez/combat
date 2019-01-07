<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat\CombatActions;

use HeroesofAbenez\Combat\Character;
use HeroesofAbenez\Combat\CombatBase;
use Nexendrie\Utils\Numbers;
use HeroesofAbenez\Combat\Team;

final class Heal implements ICombatAction {
  use \Nette\SmartObject;

  public const ACTION_NAME = "healing";

  public function getName(): string {
    return static::ACTION_NAME;
  }

  public function getPriority(): int {
    return 1000;
  }

  public function shouldUse(CombatBase $combat, Character $character): bool {
    return (in_array($character, $this->findHealers($combat)->toArray(), true) AND !is_null($this->selectHealingTarget($character, $combat)));
  }

  public function do(CombatBase $combat, Character $character): void {
    $result = [];
    /** @var Character $patient */
    $patient = $this->selectHealingTarget($character, $combat);
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

  protected function findHealers(CombatBase $combat): Team {
    $healers = call_user_func($combat->healers, $combat->team1, $combat->team2);
    if($healers instanceof Team) {
      return $healers;
    }
    return new Team("healers");
  }

  protected function selectHealingTarget(Character $healer, CombatBase $combat): ?Character {
    return $combat->getTeam($healer)->getLowestHpCharacter();
  }
}
?>