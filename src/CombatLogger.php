<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Localization\ITranslator;

/**
 * Combat log
 * 
 * @author Jakub Konečný
 * @property int $round Current round
 * @property string $title
 * @property string $template
 */
final class CombatLogger implements \Countable, \IteratorAggregate {
  use \Nette\SmartObject;
  
  /** @var \Latte\Engine */
  protected $latte;
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
  /** @var string */
  protected $template = __DIR__ . "/CombatLog.latte";
  
  public function __construct(ILatteFactory $latteFactory, ITranslator $translator) {
    $this->latte = $latteFactory->create();
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
  
  public function getTemplate(): string {
    return $this->template;
  }
  
  /**
   * @throws \RuntimeException
   */
  public function setTemplate(string $template): void {
    if(!is_file($template)) {
      throw new \RuntimeException("File $template does not exist.");
    }
    $this->template = $template;
  }
  
  /**
   * Adds new entry
   */
  public function log(array $action): void {
    $this->actions[$this->round][] = new CombatAction($this->translator, $action);
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
    return $this->latte->renderToString($this->template, $params);
  }
  
  public function count(): int {
    return count($this->actions);
  }
  
  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this->actions);
  }
}
?>