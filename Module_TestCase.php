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
    DIRECTORY_SEPARATOR.'Stub'.
    DIRECTORY_SEPARATOR.'Core.php'
);
require_once(
    dirname(__FILE__).
    DIRECTORY_SEPARATOR.'Stub'.
    DIRECTORY_SEPARATOR.'I18n.php'
);
require_once(
    dirname(__FILE__).
    DIRECTORY_SEPARATOR.'Stub'.
    DIRECTORY_SEPARATOR.'Styling.php'
);
require_once(
    dirname(__FILE__).
    DIRECTORY_SEPARATOR.'Interface'.
    DIRECTORY_SEPARATOR.'Connection.php'
);

abstract class  Erebot_Testenv_Module_TestCase
extends         PHPUnit_Framework_TestCase
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
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE);
        $this->_networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE);
        $this->_serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE);
        $this->_bot = $this->getMock('Erebot_Testenv_Stub_Core', array(), array($this->_mainConfig), '', FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Testenv_Interface_Connection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE);
        $this->_translator = $this->getMock('Erebot_Testenv_Stub_I18n', array(), array('', ''), '', FALSE, FALSE);
        $this->_eventHandler = $this->getMock('Erebot_Interface_EventHandler', array(), array(), '', FALSE, FALSE);
        $this->_rawHandler = $this->getMock('Erebot_Interface_RawHandler', array(), array(), '', FALSE, FALSE);

        $deps = array(
            '!Callable'         => 'Erebot_Testenv_Stub_Callable',
            '!Styling'          => 'Erebot_Testenv_Stub_Styling',
            '!Styling_Currency' => 'Erebot_Testenv_Stub_Styling_Currency',
            '!Styling_DateTime' => 'Erebot_Testenv_Stub_Styling_DateTime',
            '!Styling_Duration' => 'Erebot_Testenv_Stub_Styling_Duration',
            '!Styling_Float'    => 'Erebot_Testenv_Stub_Styling_Float',
            '!Styling_Integer'  => 'Erebot_Testenv_Stub_Styling_Integer',
            '!Styling_String'   => 'Erebot_Testenv_Stub_Styling_String',
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
    }

    protected function _injectStubs()
    {
        if ($this->_module === NULL)
            return;
        foreach ($this->_factory as $iface => $cls)
            $this->_module->setFactory($iface, $cls);
    }

    protected function _setConnectionExpectations()
    {
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

