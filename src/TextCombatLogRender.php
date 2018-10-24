<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

use Nette\Bridges\ApplicationLatte\ILatteFactory;

/**
 * TextCombatLogRender
 *
 * @property string $template
 */
final class TextCombatLogRender implements ICombatLogRender {
  use \Nette\SmartObject;

  /** @var \Latte\Engine */
  protected $latte;
  /** @var string */
  protected $template = __DIR__ . "/CombatLog.latte";

  public function __construct(ILatteFactory $latteFactory) {
    $this->latte = $latteFactory->create();
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
    return $this->latte->renderToString($this->template, $params);
  }
}
?>