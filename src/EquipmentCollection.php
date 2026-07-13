<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * @author Jakub Konečný
 * @internal
 * @extends \Nexendrie\Utils\Collection<Equipment>
 */
final class EquipmentCollection extends \Nexendrie\Utils\Collection
{
    public function __construct()
    {
        parent::__construct();
        $this->class = Equipment::class;
    }
}
