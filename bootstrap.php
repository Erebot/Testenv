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

if ('@php_dir@' == '@'.'php_dir'.'@') {
    $base = dirname(dirname(TESTENV_DIR . DIRECTORY_SEPARATOR)) .
            DIRECTORY_SEPARATOR;
    require(
        $base .
        'vendor' . DIRECTORY_SEPARATOR .
        'Erebot_API' . DIRECTORY_SEPARATOR .
        'src' . DIRECTORY_SEPARATOR .
        'Erebot' . DIRECTORY_SEPARATOR .
        'Autoload.php'
    );

    // Add the component's sources to the Autoloader.
    Erebot_Autoload::initialize($base . "src");

    // Add vendor sources too.
    $base .= "vendor";
    foreach (scandir($base) as $path) {
        if (trim($path, '.') == '')
            continue;
        $path = $base . DIRECTORY_SEPARATOR .
                $path . DIRECTORY_SEPARATOR;
        if (is_dir($path . 'src'))
            Erebot_Autoload::initialize($path . 'src');
        if (is_dir($path . 'lib'))  // for sfService.
            Erebot_Autoload::initialize($path . 'lib');
    }
    // Register include_path with the Autoloader.
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
        Erebot_Autoload::initialize($path);
}
// Otherwise, we're probably in Pyrus/PEAR.
else {
    require('Erebot/Autoload.php');
    Erebot_Autoload::initialize('@php_dir@');
}

/*
 * This is needed because PHPUnit can't mock static methods
 * when an interface is used as the source, and we do not
 * want to include the real class here because it might
 * conflict with our test classes.
 */
abstract class ErebotTestCore
implements Erebot_Interface_Core
{
    public static function getVersion()
    {
        // The version is usually left unused,
        // so we just return NULL here.
        return NULL;
    }
}

abstract class ErebotTestI18n
implements Erebot_Interface_I18n
{
    public static function nameToCategory($name)
    {
        return NULL;
    }

    public static function categoryToName($category)
    {
        return NULL;
    }
}

abstract class ErebotModuleTestCase
extends PHPUnit_Framework_TestCase
{
    protected $_outputBuffer    = array();
    protected $_mainConfig      = NULL;
    protected $_networkConfig   = NULL;
    protected $_serverConfig    = NULL;
    protected $_bot             = NULL;
    protected $_connection      = NULL;
    protected $_translator      = NULL;

    // Used to simulate a line being sent to the connection.
    public function _pushLine($line)
    {
        $this->_outputBuffer[] = $line;
    }

    // Needed because PHPUnit passes an additional NULL
    // and str*cmp will choke on it.
    public function _strcmp($a, $b)
    {
        return strcmp($a, $b);
    }

    // Needed because PHPUnit passes an additional NULL
    // and str*cmp will choke on it.
    public function _strncmp($a, $b, $n)
    {
        return strncmp($a, $b, $n);
    }

    // Needed because PHPUnit passes an additional NULL
    // and str*cmp will choke on it.
    public function _strcasecmp($a, $b)
    {
        return strcasecmp($a, $b);
    }

    // Needed because PHPUnit passes an additional NULL
    // and str*cmp will choke on it.
    public function _strncasecmp($a, $b, $n)
    {
        return strncasecmp($a, $b, $n);
    }

    public function setUp()
    {
        $this->_outputBuffer = array();
        $sxml = new SimpleXMLElement('<foo/>');

        // Create the basic pieces needed to create the module.
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE);
        $this->_networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE);
        $this->_serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE);
        $this->_bot = $this->getMock('ErebotTestCore', array(), array($this->_mainConfig), '', FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Interface_Connection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE);
        $this->_translator = $this->getMock('ErebotTestI18n', array(), array('', ''), '', FALSE, FALSE);
        $this->_eventHandler = $this->getMock('Erebot_Interface_EventHandler', array(), array(), '', FALSE, FALSE);
        $this->_rawHandler = $this->getMock('Erebot_Interface_RawHandler', array(), array(), '', FALSE, FALSE);

        // Now, add some useful behaviour to those pieces.
        $this->_connection
            ->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue($this->_bot));

        $this->_connection
            ->expects($this->any())
            ->method('pushLine')
            ->will($this->returnCallback(array($this, '_pushLine')));

        $this->_connection
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->_serverConfig));

        $this->_connection
            ->expects($this->any())
            ->method('irccmp')
            ->will($this->returnCallback(array($this, '_strcmp')));

        $this->_connection
            ->expects($this->any())
            ->method('ircncmp')
            ->will($this->returnCallback(array($this, '_strncmp')));

        $this->_connection
            ->expects($this->any())
            ->method('irccasecmp')
            ->will($this->returnCallback(array($this, '_strcasecmp')));

        $this->_connection
            ->expects($this->any())
            ->method('ircncasecmp')
            ->will($this->returnCallback(array($this, '_strncasecmp')));

        $this->_connection
            ->expects($this->any())
            ->method('normalizeNick')
            ->will($this->returnArgument(0));

        $this->_networkConfig
            ->expects($this->any())
            ->method('getMainCfg')
            ->will($this->returnValue($this->_mainConfig));

        $this->_networkConfig
            ->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($this->_translator));

        $this->_serverConfig
            ->expects($this->any())
            ->method('getMainCfg')
            ->will($this->returnValue($this->_mainConfig));

        $this->_serverConfig
            ->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($this->_translator));

        $this->_serverConfig
            ->expects($this->any())
            ->method('getNetworkConfig')
            ->will($this->returnValue($this->_networkConfig));

        $this->_mainConfig
            ->expects($this->any())
            ->method('getMainCfg')
            ->will($this->returnValue($this->_mainConfig));

        $this->_mainConfig
            ->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($this->_translator));

        $this->_mainConfig
            ->expects($this->any())
            ->method('getCommandsPrefix')
            ->will($this->returnValue('!'));

        $this->_translator
            ->expects($this->any())
            ->method('gettext')
            ->will($this->returnArgument(0));
    }
}

// Configure Plop if possible.
if (class_exists('Plop', TRUE)) {
    $logging =& Plop::getInstance();
    $logging->basicConfig();
    unset($logging);
}

// Preload some of the classes.
foreach (
    array(
        'Erebot_NotFoundException',
        'Erebot_NotImplementedException',
        'Erebot_ErrorReportingException',
        'Erebot_ConnectionFailureException',
        'Erebot_IllegalActionException',
        'Erebot_InvalidValueException',
        'Erebot_Interface_I18n',
        'Erebot_Interface_Timer',
        'Erebot_Interface_EventHandler',
        'Erebot_Interface_RawHandler',
        'Erebot_Interface_Config_Main',
        'Erebot_Interface_Core',
        'Erebot_Interface_Connection',
        'Erebot_Interface_Config_Server',
        'Erebot_Interface_Config_Network',
        'Erebot_Module_Base',
    ) as $preload)
    if (!class_exists($preload, TRUE) &&
        !interface_exists($preload, TRUE))
        throw new Exception('Could not preload "'.$preload.'"');
unset($preload);

