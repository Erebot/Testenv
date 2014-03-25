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

$stubs = array(
    'Callable',
    'I18n',
    'Identity',
    'IrcCollator',
    'Styling',
    'Styling' . DIRECTORY_SEPARATOR . 'Variable',
);

foreach ($stubs as $stub) {
    require_once(
        dirname(__FILE__).
        DIRECTORY_SEPARATOR.'Stub'.
        DIRECTORY_SEPARATOR.$stub.'.php'
    );
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestCase.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'HelperModule.php');

// Preload some of the interfaces.
// This is required somehow to make PHPUnit & phing happy.
interface_exists('\\Erebot\\Interfaces\\Config\\Network');
interface_exists('\\Erebot\\Interfaces\\Config\\Server');
interface_exists('\\Erebot\\Interfaces\\EventHandler');
interface_exists('\\Erebot\\Interfaces\\Config\\Main');
interface_exists('\\Erebot\\Interfaces\\NumericHandler');
interface_exists('\\Erebot\\Interfaces\\TextWrapper');

abstract class  Erebot_Testenv_Module_TestCase
extends         Erebot_Testenv_TestCase
{
    protected $_outputBuffer    = array();
    protected $_mainConfig      = NULL;
    protected $_networkConfig   = NULL;
    protected $_serverConfig    = NULL;
    protected $_bot             = NULL;
    protected $_connection      = NULL;
    protected $_translator      = NULL;
    protected $_factory         = array();
    protected $_module          = NULL;
    protected $_modules         = array();

    // Used to simulate a line being sent to the connection.
    public function push($line)
    {
        $this->_outputBuffer[] = $line;
    }

    public function setUp()
    {
        $this->_outputBuffer    = array();
        $this->_collator        = new Erebot_Testenv_Stub_IrcCollator();

        $this->_createMocks();
        $this->_setConnectionExpectations();
        $this->_setNetworkConfigExpectations();
        $this->_setServerConfigExpectations();
        $this->_setMainConfigExpectations();
        $this->_setTranslatorExpectations();
        $this->_injectStubs();
    }

    protected function _createMocks()
    {
        $sxml = new SimpleXMLElement('<foo/>');

        // Build the basic pieces needed to create the module.
        $this->_mainConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Main', array(), array(), '', FALSE, FALSE);
        $this->_networkConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE);
        $this->_serverConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE);
        $this->_bot = $this->getMock('\\Erebot\\Interfaces\\Core', array(), array($this->_mainConfig), '', FALSE, FALSE);
        $this->_connection = $this->getMock('\\Erebot\\Interfaces\\IrcConnection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE);
        $this->_translator = $this->getMock('Erebot_Testenv_Stub_I18n', array(), array('', ''), '', FALSE, FALSE);
        $this->_eventHandler = $this->getMock('\\Erebot\\Interfaces\\EventHandler', array(), array(), '', FALSE, FALSE);
        $this->_numericHandler = $this->getMock('\\Erebot\\Interfaces\\NumericHandler', array(), array(), '', FALSE, FALSE);

        $deps = array(
            '!Callable'                     => 'Erebot_Testenv_Stub_Callable',
            '!Identity'                     => 'Erebot_Testenv_Stub_Identity',
            '!Styling\\Variables\\Currency' => 'Erebot_Testenv_Stub_Styling_Currency',
            '!Styling\\Variables\\DateTime' => 'Erebot_Testenv_Stub_Styling_DateTime',
        );

        foreach ($deps as $dep => $acls) {
            $mock = $this->getMockForAbstractClass(
                $acls,
                array(),
                '',
                FALSE,
                FALSE
            );
            $this->_factory[$dep] = get_class($mock);
        }

        $mock = $this->getMock(
            'Erebot_Testenv_Stub_Styling',
            array('__construct'),
            array(),
            '',
            FALSE,
            FALSE
        );
        $this->_factory['!Styling'] = get_class($mock);

        $deps = array(
            '!Styling\\Variables\\Duration' => 'Erebot_Testenv_Stub_Styling_Duration',
            '!Styling\\Variables\\Float'    => 'Erebot_Testenv_Stub_Styling_Float',
            '!Styling\\Variables\\Integer'  => 'Erebot_Testenv_Stub_Styling_Integer',
            '!Styling\\Variables\\String'   => 'Erebot_Testenv_Stub_Styling_String',
        );

        foreach ($deps as $dep => $acls) {
            $mock = $this->getMockForAbstractClass(
                $acls,
                array(),
                '',
                TRUE,
                FALSE
            );
            $this->_factory[$dep] = get_class($mock);
        }

        $this->_modules['\\Erebot\\Module\\Helper'] =
            new Erebot_Testenv_HelperModule();
    }

    protected function _injectStubs()
    {
        if ($this->_module === NULL)
            return;
        foreach ($this->_factory as $iface => $cls)
            $this->_module->setFactory($iface, $cls);
    }

    public function _isChannel($chan)
    {
        return !strncmp($chan, '#', 1);
    }

    public function _getModule($name, $chan = null, $autoload = true)
    {
        if (!isset($this->_modules[$name])) {
            throw new \Erebot\NotFoundException("No instance of $name found");
        }
        return $this->_modules[$name];
    }

    protected function _setConnectionExpectations()
    {
        $this->_connection
            ->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue($this->_bot));

        $this->_connection
            ->expects($this->any())
            ->method('getIO')
            ->will($this->returnValue($this));

        $this->_connection
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->_serverConfig));

        $this->_connection
            ->expects($this->any())
            ->method('getCollator')
            ->will($this->returnValue($this->_collator));

        $this->_connection
            ->expects($this->any())
            ->method('isChannel')
            ->will($this->returnCallback(array($this, '_isChannel')));

        $this->_connection
            ->expects($this->any())
            ->method('getModule')
            ->will($this->returnCallback(array($this, '_getModule')));
    }

    protected function _setNetworkConfigExpectations()
    {
        $this->_networkConfig
            ->expects($this->any())
            ->method('getMainCfg')
            ->will($this->returnValue($this->_mainConfig));

        $this->_networkConfig
            ->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($this->_translator));
    }

    protected function _setServerConfigExpectations()
    {
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
    }

    protected function _setMainConfigExpectations()
    {
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
    }

    protected function _setTranslatorExpectations()
    {
        $this->_translator
            ->expects($this->any())
            ->method('gettext')
            ->will($this->returnArgument(0));
    }
}

