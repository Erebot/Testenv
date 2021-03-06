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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Utils.php');

abstract class  Erebot_Testenv_TestCase
extends         \PHPUnit\Framework\TestCase
{
    private $expectedLogs = NULL;
    private $logStream = NULL;

    public function getExpectedLogs()
    {
        return $this->expectedLogs;
    }

    public function addExpectedLog($logLine)
    {
        if (!is_string($logLine)) {
            throw new Exception();
        }

        if ($this->expectedLogs === NULL) {
            $this->expectedLogs = array();
        }

        $this->expectedLogs[] = $logLine;
    }

    public function setExpectedLogs($logLines)
    {
        if (is_string($logLines)) {
            $logLines = trim($logLines);
            $logLines = preg_split('/\\r?\\n/', $logLines);
        }

        if ($logLines === NULL) {
            return;
        }

        if (!is_array($logLines)) {
            throw new Exception();
        }

        $this->expectedLogs = array();
        foreach ($logLines as $logLine) {
            $this->addExpectedLog($logLine);
        }
    }

    protected function setExpectedLogsFromAnnotations()
    {
        try {
            $expectedLogs = Erebot_Testenv_Utils::getExpectedLogs(
                get_class($this),
                $this->getName()
            );

            $this->setExpectedLogs($expectedLogs);
        }

        catch (ReflectionException $e) {
        }
    }

    protected function runTest()
    {
        $this->setExpectedLogsFromAnnotations();

        $logging            = \Plop\Plop::getInstance();
        $this->logStream    = fopen('php://temp', 'a+');

        $handlers   = new \Plop\HandlersCollection();
        $handler    = new \Plop\Handler\Stream($this->logStream);
        $handler->setFormatter(
            new \Plop\Formatter('%(levelname)s:%(message)s')
        );
        $handlers[] = $handler;
        $logging->getLogger()->setHandlers($handlers);

        $result = parent::runTest();

        if ($this->expectedLogs !== NULL) {
            fseek($this->logStream, 0);
            $actualLogs = stream_get_contents($this->logStream);
            fclose($this->logStream);
            $actualLogs = array_map('rtrim', explode("\n", $actualLogs));
            $actualLogs = array_values(array_filter($actualLogs, 'strlen'));

            $this->addToAssertionCount(1);
            if (count($this->expectedLogs)) {
                $this->assertEquals($this->expectedLogs, $actualLogs);
            }

            else if (count($actualLogs)) {
                $this->fail(
                    "No logs expected, but we received:\n" .
                    var_export($actualLogs, TRUE)
                );
            }
        }

        return $result;
    }
}

