<?php

// Avoid harmless warning on some
// badly-configured PHP installations.
date_default_timezone_set('UTC');

function Erebot_testenv_autoloader($className)
{
    $class = ltrim($className, '\\');
    $path = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class);

    // When running from the trunk.
    if ('@php_dir@' == '@'.'php_dir'.'@') {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $repos_root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        if (basename($repos_root) == 'modules')
            $repos_root = dirname($repos_root);
        if (count($parts) > 2 && $parts[2] != 'Base' &&
            array_slice($parts, 0, 2) == array('Erebot', 'Module'))
            array_unshift($parts, $repos_root, 'modules', $parts[2], 'trunk', 'src');
        else if ($parts[0] == 'Plop')
            array_unshift($parts, $repos_root, 'logging', 'trunk', 'src');
        else if ($parts[0] == 'Erebot')
            array_unshift($parts, $repos_root, 'core', 'trunk', 'src');
        // Otherwise, the path gets restored to its previous state,
        // where PHP uses the usual include_path to locate the file.
        $path = implode(DIRECTORY_SEPARATOR, $parts);
    }
    // When running from a pear/pyrus installation.
    else array_unshift($parts, '@php_dir@');

    $path .= '.php';
    if (($fp = @fopen($path, 'rb', TRUE)) !== FALSE) {
        fclose($fp);
        unset($fp);
        include_once($path);
    }
}

// Register the autoloader, using whatever's available.
if (function_exists('spl_autoload_register'))
    spl_autoload_register('Erebot_testenv_autoloader');
else {
    function __autoload($class)
    {
        return Erebot_testenv_autoloader($class);
    }
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
        return NULL;
    }
}

abstract class ErebotModuleTestCase
extends PHPUnit_Framework_TestCase
{
    protected $_outputBuffer = array();
    protected $_mainConfig = NULL;
    protected $_networkConfig = NULL;
    protected $_serverConfig = NULL;
    protected $_bot = NULL;
    protected $_connection = NULL;
    protected $_translator = NULL;

    public function _pushLine($line)
    {
        $this->_outputBuffer[] = $line;
    }

    public function setUp()
    {
        $this->_outputBuffer = array();
        $sxml = new SimpleXMLElement('<foo/>');
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE, FALSE);
        $this->_networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_bot = $this->getMock('ErebotTestCore', array(), array($this->_mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Interface_Connection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE, FALSE);
        $this->_translator = $this->getMock('Erebot_Interface_I18n', array(), array('', ''), '', FALSE, FALSE, FALSE);

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
            ->will($this->returnValue($this->_networkConfig));

        $this->_connection
            ->expects($this->any())
            ->method('irccmp')
            ->will($this->returnCallback('strcmp'));

        $this->_connection
            ->expects($this->any())
            ->method('ircncmp')
            ->will($this->returnCallback('strncmp'));

        $this->_connection
            ->expects($this->any())
            ->method('irccasecmp')
            ->will($this->returnCallback('strcasecmp'));

        $this->_connection
            ->expects($this->any())
            ->method('ircncasecmp')
            ->will($this->returnCallback('strncasecmp'));

        $this->_networkConfig
            ->expects($this->any())
            ->method('getMainCfg')
            ->will($this->returnValue($this->_mainConfig));

        $this->_networkConfig
            ->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($this->_translator));

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
        'Erebot_Interface_Event_Raw',
        'Erebot_Interface_EventHandler',
        'Erebot_Interface_RawHandler',
        'Erebot_Interface_Config_Main',
        'Erebot_Interface_Core',
        'Erebot_Interface_Connection',
        'Erebot_Interface_Config_Server',
        'Erebot_Interface_Config_Network',
        'Erebot_Interface_Connection',
        'Erebot_Interface_Event_Generic',
        'Erebot_Interface_Event_Raw',
        'Erebot_Module_Base',
    ) as $preload)
    if (!class_exists($preload, TRUE) &&
        !interface_exists($preload, TRUE))
        throw new Exception('Could not preload "'.$preload.'"');
unset($preload);
