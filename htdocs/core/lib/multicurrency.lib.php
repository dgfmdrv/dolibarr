<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/multicurrency.lib.php
 *	\ingroup	multicurency
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * Prepare array with list of tabs
 *
 * @return  array    Array of tabs to show
 */
function multicurrencyAdminPrepareHead()
{
    global $langs, $conf;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/admin/multicurrency.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;

    complete_head_from_modules($conf, $langs, null, $head, $h, 'multicurrency');

    return $head;
}
