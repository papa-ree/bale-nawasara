<?php

namespace Paparee\BaleNawasara\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Paparee\BaleNawasara\BaleNawasara
 */
class BaleNawasara extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Paparee\BaleNawasara\BaleNawasara::class;
    }
}
