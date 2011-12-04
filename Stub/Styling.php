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

require_once(
    dirname(__FILE__).
    DIRECTORY_SEPARATOR.'Styling'.
    DIRECTORY_SEPARATOR.'Variable.php'
);

abstract class  Erebot_Testenv_Stub_Styling
implements      Erebot_Interface_Styling
{
    protected $_translator = NULL;

    public function __construct(Erebot_Interface_I18n $translator)
    {
        $this->_translator = $translator;
    }

    public function _($msg, array $vars = array())
    {
        return $this->render($msg, $vars);
    }

    public function render($msg, array $vars = array())
    {
        $subst = array();
        foreach ($vars as $name => $value) {
            if (!is_array($value)) {
                if (is_object($value) &&
                    ($value instanceof Erebot_Interface_Styling_Variable))
                    $value = $value->render($this->_translator);

                $subst['name="'.$name.'"'] = 'value="'.$value.'"';
                $subst["name='".$name."'"] = 'value="'.$value.'"';
            }
            else {
                $subst['from="'.$name.'"'] =
                    'from="'.var_export($value, TRUE).'"';
                $subst["from='".$name."'"] =
                    'from="'.var_export($value, TRUE).'"';
            }
        }

        return strtr($msg, $subst);
    }

    public function getTranslator()
    {
        return $this->_translator;
    }
}


