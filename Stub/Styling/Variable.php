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

abstract class  Erebot_Testenv_Stub_Styling_Variable
{
    protected $_args;

    public function __construct()
    {
        $this->_args = func_get_args();
    }

    public function render(Erebot_Interface_I18n $translator)
    {
        return var_export($this->_args, TRUE);
    }
}

abstract class  Erebot_Testenv_Stub_Styling_String
implements      Erebot_Interface_Styling_String
{
}

abstract class  Erebot_Testenv_Stub_Styling_Integer
implements      Erebot_Interface_Styling_Integer
{
}

abstract class  Erebot_Testenv_Stub_Styling_Float
implements      Erebot_Interface_Styling_Float
{
}

abstract class  Erebot_Testenv_Stub_Styling_DateTime
implements      Erebot_Interface_Styling_DateTime
{
}

abstract class  Erebot_Testenv_Stub_Styling_Duration
implements      Erebot_Interface_Styling_Duration
{
}

abstract class  Erebot_Testenv_Stub_Styling_Currency
implements      Erebot_Interface_Styling_Currency
{
}

