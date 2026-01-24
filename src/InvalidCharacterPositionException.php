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
    public const int ROW_FULL = 1;
    public const int POSITION_OCCUPIED = 2;
}
