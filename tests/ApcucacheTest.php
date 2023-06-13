<?php

namespace Pitk\Tests;

use PHPUnit\Framework\TestCase;
use Pitk\Pitk;


/**
 * @requires extension apcu
 */

class ApcucacheTest extends TestCase {
    protected $dbm; // Pitk 实例


    function setUp():void {
        ini_set('apc.enable_cli', 1);
        $this->dbm = new Pitk();
     }


     function testApcucache1():void {
         $cache = $this->dbm->apcucache();

         $cache->set('key_1', 'dat_1');
         self::assertEquals('dat_1', $cache->get('key_1'));

         $cache->delete('key_1');
         self::assertNull($cache->get('key_1'));

         $default = 'dat_9';
         self::assertEquals($default, $cache->get('key_9', $default));

         $cache->set('key_9', 'dat_9');
         self::assertTrue($cache->has('key_9'));
         $cache->clear();
         self::assertNull($cache->get('key_9'));
         self::assertFalse($cache->has('key_9'));

     }

     function testApcucache2():void {
         $cache = $this->dbm->apcucache();

         $cache->set('key_1', 'dat_1', 1); // ttl = 1
         self::assertTrue($cache->has('key_1'));
         usleep(2000000); // Wait 2 seconds so the cache expires
         self::assertFalse($cache->has('key_1'));

         $cache->set('key_2', 'dat_2', new \DateInterval('PT1S')); // ttl = 1
         self::assertTrue($cache->has('key_2'));
         usleep(2000000); // Wait 2 seconds so the cache expires
         self::assertFalse($cache->has('key_2'));
     }


    function testApcucache3():void {
        $cache = $this->dbm->apcucache();

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache->setMultiple($values);
        $result = $cache->getMultiple(array_keys($values));
        foreach ($result as $key => $value) {
            self::assertTrue(isset($values[$key]));
            self::assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $values);
    }

    function testApcucache4():void {
        $cache = $this->dbm->apcucache();

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache->setMultiple($values, new \DateInterval('PT1S'));
        sleep(2); // Wait 2 seconds so the cache expires
        $result = $cache->getMultiple(array_keys($values), 'not-found');
        $count = 0;

        $expected = [
            'key1' => 'not-found',
            'key2' => 'not-found',
            'key3' => 'not-found',
        ];

        foreach ($result as $key => $value) {
            ++$count;
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        self::assertEquals(3, $count);

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    function testApcucache5():void {
        $cache = $this->dbm->apcucache();

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache->setMultiple($values);
        $cache->deleteMultiple(['key1', 'key3']);
        $result = $cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    //cls.end
}
