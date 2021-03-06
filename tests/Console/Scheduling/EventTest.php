<?php

namespace Illuminate\Tests\Console\Scheduling;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Console\Scheduling\Event;

class EventTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBuildCommand()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');

        $defaultOutput = (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
        $this->assertSame("php -i > {$quote}{$defaultOutput}{$quote} 2>&1", $event->buildCommand());

        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');
        $event->runInBackground();

        $defaultOutput = (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
        $this->assertSame("(php -i > {$quote}{$defaultOutput}{$quote} 2>&1 ; '".PHP_BINARY."' artisan schedule:finish \"framework/schedule-eeb46c93d45e928d62aaf684d727e213b7094822\") > {$quote}{$defaultOutput}{$quote} 2>&1 &", $event->buildCommand());
    }

    public function testBuildCommandSendOutputTo()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');

        $event->sendOutputTo('/dev/null');
        $this->assertSame("php -i > {$quote}/dev/null{$quote} 2>&1", $event->buildCommand());

        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');

        $event->sendOutputTo('/my folder/foo.log');
        $this->assertSame("php -i > {$quote}/my folder/foo.log{$quote} 2>&1", $event->buildCommand());
    }

    public function testBuildCommandAppendOutput()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');

        $event->appendOutputTo('/dev/null');
        $this->assertSame("php -i >> {$quote}/dev/null{$quote} 2>&1", $event->buildCommand());
    }

    public function testNextRunDate()
    {
        $event = new Event(m::mock('Illuminate\Console\Scheduling\EventMutex'), 'php -i');
        $event->dailyAt('10:15');

        $this->assertSame('10:15:00', $event->nextRunDate()->toTimeString());
    }
}
