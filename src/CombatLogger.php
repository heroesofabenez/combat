<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Localization\ITranslator;

/**
 * Combat log
 * 
 * @author Jakub Konečný
 */
final class CombatLogger implements \Countable, \IteratorAggregate, \Stringable {
  use \Nette\SmartObject;

  protected ICombatLogRender $render;
  protected ITranslator $translator;
  protected Team $team1;
  protected Team $team2;
  protected array $actions = [];
  public int $round = 0;
  public string $title = "";
  
  public function __construct(ICombatLogRender $render, ITranslator $translator) {
    $this->render = $render;
    $this->translator = $translator;
  }
  
  /**
   * Set teams
   */
  public function setTeams(Team $team1, Team $team2): void {
    if(isset($this->team1)) {
      throw new ImmutableException("Teams has already been set.");
    }
    $this->team1 = $team1;
    $this->team2 = $team2;
  }

  /**
   * Adds new entry
   */
  public function log(array $action): void {
    $this->actions[$this->round][] = new CombatLogEntry($action);
  }
  
  /**
   * Adds text entry
   */
  public function logText(string $text, array $params = []): void {
    $this->actions[$this->round][] = $this->translator->translate($text, 0, $params);
  }
  
  public function __toString(): string {
    $params = [
      "team1" => $this->team1, "team2" => $this->team2, "actions" => $this->actions, "title" => $this->title,
    ];
    return $this->render->render($params);
  }
  
  public function count(): int {
    return count($this->actions);
  }
  
  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this->actions);
  }
}
?>