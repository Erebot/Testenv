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

abstract class  Erebot_Testenv_Stub_Identity
implements      Erebot_Interface_Identity
{
    protected $_nick;
    protected $_ident;
    protected $_host;

    public function __construct($user)
    {
        $nick = substr($user, 0, strcspn($user, '!'));
        $user = (string) substr($user, strcspn($user, '!') + 1);
        $ident = substr($user, 0, strcspn($user, '@'));
        $user = (string) substr($user, strcspn($user, '@') + 1);

        $this->_nick    = $nick;
        $this->_ident   = $ident;
        $this->_host    = $user;
    }

    public function getNick()
    {
        return $this->_nick;
    }

    public function getIdent()
    {
        return $this->_ident;
    }

    public function getHost()
    {
        return $this->_host;
    }
}

