<?php
namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;
use DTApi\Helpers\TeHelper;

class WillExpireAtTest extends TestCase
{
    public function testWillExpireAtWithDifferenceWithin90Minutes()
    {
        $due_time = Carbon::now()->addHours(1);
        $created_at = Carbon::now();

        $expected = $due_time->format('Y-m-d H:i:s');
        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($expected, $actual);
    }

    public function testWillExpireAtWithDifferenceBetween90MinutesAnd24Hours()
    {
        $due_time = Carbon::now()->addHours(2);
        $created_at = Carbon::now()->subMinutes(30);

        $expected = $created_at->addMinutes(90)->format('Y-m-d H:i:s');
        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($expected, $actual);
    }

    public function testWillExpireAtWithDifferenceBetween24And72Hours()
    {
        $due_time = Carbon::now()->addDays(3);
        $created_at = Carbon::now()->subDays(2);

        $expected = $created_at->addHours(16)->format('Y-m-d H:i:s');
        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($expected, $actual);
    }

    public function testWillExpireAtWithDifferenceMoreThan72Hours()
    {
        $due_time = Carbon::now()->subDays(1);
        $created_at = Carbon::now()->subDays(5);

        $expected = $due_time->subHours(48)->format('Y-m-d H:i:s');
        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($expected, $actual);
    }
}
