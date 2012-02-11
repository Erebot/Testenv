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

abstract class  Erebot_Testenv_Stub_Callable
implements      Erebot_Interface_Callable
{
    protected $_callable;

    public function __construct($callable)
    {
        $this->_callable = $callable;
    }

    public function invoke()
    {
        $args = func_get_args();
        return call_user_func_array($this->_callable, $args);
    }

    public function getCallable()
    {
        return $this->_callable;
    }
}

