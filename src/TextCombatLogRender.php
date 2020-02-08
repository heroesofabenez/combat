<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Localization\ITranslator;
use HeroesofAbenez\Combat\CombatActions;

/**
 * TextCombatLogRender
 *
 * @property-write string $template
 */
final class TextCombatLogRender implements ICombatLogRender {
  use \Nette\SmartObject;

  /** @var \Latte\Engine */
  protected $latte;
  /** @var ITranslator */
  protected $translator;
  /** @var string */
  protected $template = __DIR__ . "/CombatLog.latte";

  public function __construct(ILatteFactory $latteFactory, ITranslator $translator) {
    $this->latte = $latteFactory->create();
    $this->translator = $translator;
  }

  /**
   * @throws \RuntimeException
   */
  protected function setTemplate(string $template): void {
    if(!is_file($template)) {
      throw new \RuntimeException("File $template does not exist.");
    }
    $this->template = $template;
  }

  public function render(array $params): string {
    $params["render"] = $this;
    return $this->latte->renderToString($this->template, $params);
  }

  /**
   * @param CombatLogEntry|string $item
   */
  public function renderItem($item): string {
    if(!$item instanceof CombatLogEntry) {
      return $item;
    }
    $character1 = $item->character1->name;
    $character2 = $item->character2->name;
    switch($item->action) {
      case CombatActions\Attack::ACTION_NAME:
        $message = ($item->result) ? "combat.log.attackHits" : "combat.log.attackFails";
        $text = $this->translator->translate($message, $item->amount, ["character1" => $character1, "character2" => $character2]);
        if($item->result && $item->character2->hitpoints < 1) {
          $text .= $this->translator->translate("combat.log.characterFalls");
        }
        return $text;
      case CombatActions\SkillAttack::ACTION_NAME:
        $message = ($item->result) ? "combat.log.specialAttackHits" : "combat.log.specialAttackFails";
        $text = $this->translator->translate($message, $item->amount, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
        if($item->result && $item->character2->hitpoints < 1) {
          $text .= $this->translator->translate("combat.log.characterFalls");
        }
        return $text;
      case CombatActions\SkillSpecial::ACTION_NAME:
        $message = ($item->result) ? "combat.log.specialSkillSuccess" : "combat.log.specialSKillFailure";
        return $this->translator->translate($message, 0, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
      case CombatActions\Heal::ACTION_NAME:
        $message = ($item->result) ? "combat.log.healingSuccess" : "combat.log.healingFailure";
        return $this->translator->translate($message, $item->amount, ["character1" => $character1, "character2" => $character2]);
      case CombatLogEntry::ACTION_POISON:
        return $this->translator->translate("combat.log.poison", $item->amount, ["character1" => $character1]);
    }
    return "";
  }
}
?>