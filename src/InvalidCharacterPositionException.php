<?php
declare(strict_types=1);

namespace HeroesofAbenez\Combat;

/**
 * InvalidCharacterPositionException
 *
 * @author Jakub Konečný
 */
class InvalidCharacterPositionException extends \RuntimeException
{
    public const ROW_FULL = 1;
    public const POSITION_OCCUPIED = 2;
}
