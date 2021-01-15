<?php

namespace Savannabits\Pagetables;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Savannabits\Pagetables\Pagetables
 */
class PagetablesFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-pagetables';
    }
}
