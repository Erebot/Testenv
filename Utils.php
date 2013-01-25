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

class Erebot_Testenv_Utils
{
    const REGEX_EXPECTED_LOG = '/@expectedLog\\s+(?P<line>.+)\\s*$/m';

    public static function getExpectedLogs($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();
        $logLines   = array();

        if (strpos($docComment, '@noExpectedLogs') !== FALSE) {
            return array();
        }

        if ($count = preg_match_all(self::REGEX_EXPECTED_LOG, $docComment, $matches)) {
            for ($i = 0; $i < $count; $i++) {
                $logLines[] = $matches['line'][$i];
            }
        }

        if (!count($logLines)) {
            return NULL;
        }

        return $logLines;
    }
}
