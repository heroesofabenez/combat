<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Localization\ITranslator;

/**
 * TextCombatLogRender
 *
 * @property string $template
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
  public function setTemplate(string $template): void {
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
    $text = "";
    switch($item->action) {
      case CombatLogEntry::ACTION_ATTACK:
        if($item->result) {
          $text = $this->translator->translate("combat.log.attackHits", $item->amount, ["character1" => $character1, "character2" => $character2]);
          if($item->character2->hitpoints < 1) {
            $text .= $this->translator->translate("combat.log.characterFalls");
          }
        } else {
          $text = $this->translator->translate("combat.log.attackFails", $item->amount, ["character1" => $character1, "character2" => $character2]);
        }
        break;
      case CombatLogEntry::ACTION_SKILL_ATTACK:
        if($item->result) {
          $text = $this->translator->translate("combat.log.specialAttackHits", $item->amount, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
          if($item->character2->hitpoints < 1) {
            $text .= $this->translator->translate("combat.log.characterFalls");
          }
        } else {
          $text = $this->translator->translate("combat.log.specialAttackFails", $item->amount, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
        }
        break;
      case CombatLogEntry::ACTION_SKILL_SPECIAL:
        if($item->result) {
          $text = $this->translator->translate("combat.log.specialSkillSuccess", 0, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
        } else {
          $text = $this->translator->translate("combat.log.specialSKillFailure", 0, ["character1" => $character1, "character2" => $character2, "name" => $item->name]);
        }
        break;
      case CombatLogEntry::ACTION_HEALING:
        if($item->result) {
          $text = $this->translator->translate("combat.log.healingSuccess", $item->amount, ["character1" => $character1, "character2" => $character2]);
        } else {
          $text = $this->translator->translate("combat.log.healingFailure", $item->amount, ["character1" => $character1, "character2" => $character2]);
        }
        break;
      case CombatLogEntry::ACTION_POISON:
        $text = $this->translator->translate("combat.log.poison", $item->amount, ["character1" => $character1]);
        break;
    }
    return $text;
  }
}
?>