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

/**
 * One interface to rule them all,
 * one interface to pass the tests,
 * one interface to mock them all
 * and in the unit tests bind them. */
interface   Erebot_Testenv_Interface_Connection
extends     Erebot_Interface_ModuleContainer,
            Erebot_Interface_EventDispatcher,
            Erebot_Interface_BidirectionalConnection,
            Erebot_Interface_Collated
{
    /// @FIXME: this probably belongs to the main API rather than the tests.
    public function getRawProfileLoader();

    public function setRawProfileLoader(
        Erebot_Interface_RawProfileLoader $loader
    );

    public function isChannel($chan);
}

