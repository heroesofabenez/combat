<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Localization\ITranslator;

/**
 * Combat log
 * 
 * @author Jakub Konečný
 * @property int $round Current round
 * @property string $title
 */
final class CombatLogger implements \Countable, \IteratorAggregate {
  use \Nette\SmartObject;

  /** @var ICombatLogRender */
  protected $render;
  /** @var ITranslator */
  protected $translator;
  /** @var Team First team */
  protected $team1;
  /** @var Team Second team */
  protected $team2;
  /** @var array */
  protected $actions = [];
  /** @var int */
  protected $round;
  /** @var string */
  protected $title = "";
  
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
  
  public function getRound(): int {
    return $this->round;
  }
  
  public function setRound(int $round): void {
    $this->round = $round;
  }
  
  public function getTitle(): string {
    return $this->title;
  }
  
  public function setTitle(string $title): void {
    $this->title = $title;
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