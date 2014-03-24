<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

/* This is needed because PHPUnit can't mock static methods
 * when an interface is used as the source, and we do not
 * want to include the real class here because it might
 * conflict with our test classes.
 */
abstract class  Erebot_Testenv_Stub_Core
implements      \Erebot\Interfaces\Core
{
    public static function getVersion()
    {
        // The version is usually left unused,
        // so we just return NULL here.
        return NULL;
    }
}


