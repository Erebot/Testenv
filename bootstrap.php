<?php

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

