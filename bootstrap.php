<?php

if (!defined('__DIR__')) {
  class __FILE_CLASS__ {
    function  __toString() {
      $X = debug_backtrace();
      return dirname($X[1]['file']);
    }
  }
  define('__DIR__', new __FILE_CLASS__);
} 

set_include_path(__DIR__.'/../Core'.PATH_SEPARATOR.get_include_path());

include_once(__DIR__.'/../Core/src/ifaces/i18n.php');
include_once(__DIR__.'/../Core/src/ifaces/timer.php');
include_once(__DIR__.'/../Core/src/ifaces/raw.php');
include_once(__DIR__.'/../Core/src/ifaces/eventHandler.php');
include_once(__DIR__.'/../Core/src/ifaces/rawHandler.php');
include_once(__DIR__.'/../Core/src/ifaces/mainConfig.php');
include_once(__DIR__.'/../Core/src/ifaces/core.php');
include_once(__DIR__.'/../Core/src/ifaces/connection.php');
include_once(__DIR__.'/../Core/src/ifaces/serverConfig.php');
include_once(__DIR__.'/../Core/src/ifaces/networkConfig.php');
include_once(__DIR__.'/../Core/src/ifaces/connection.php');
include_once(__DIR__.'/../Core/src/events/events.php');
include_once(__DIR__.'/../Core/src/utils.php');
include_once(__DIR__.'/../Core/src/styling.php');
include_once(__DIR__.'/../Core/src/moduleBase.php');

abstract class ErebotTestCore
implements iErebot
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
        $this->_mainConfig = $this->getMock('iErebotMainConfig', array(), array(), '', FALSE, FALSE, FALSE);
        $this->_networkConfig = $this->getMock('iErebotNetworkConfig', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_serverConfig = $this->getMock('iErebotServerConfig', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_bot = $this->getMock('ErebotTestCore', array(), array($this->_mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('iErebotConnection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE, FALSE);
        $this->_translator = $this->getMock('iErebotI18n', array(), array('', ''), '', FALSE, FALSE, FALSE);

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

        $this->_translator
            ->expects($this->any())
            ->method('gettext')
            ->will($this->returnArgument(0));
    }
}

