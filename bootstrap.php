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

// Avoid harmless warning on some
// badly-configured PHP installations.
date_default_timezone_set('UTC');

include(
    dirname(dirname(dirname(__FILE__))) .
    DIRECTORY_SEPARATOR .
    "autoload.php"
);

// HACK: backward compatibility with PHPUnit releases that lacked namespaces.
if (!class_exists('PHPUnit\\Framework\\TestResult')) {
    class_alias('PHPUnit_Framework_TestResult', 'PHPUnit\\Framework\\TestResult');
}
if (!class_exists('PHPUnit\\Framework\\TestCase')) {
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\\Framework\\TestCase');
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Module_TestCase.php');

