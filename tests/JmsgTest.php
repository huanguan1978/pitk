<?php

namespace Pitk\Tests;

use PHPUnit\Framework\TestCase;
use Pitk\{Pitk, Jmsg};

class JmsgTest extends TestCase {
    protected $dbm; // Pitk 实例


    function setUp():void {
        $this->dbm = new Pitk();
     }
    /*
    function testUnpack():void {
        $jarr = ['ns'=>'Pitk\Jmsg', 'method'=>'unpack', 'params'=>['nm'=>'crown', ], ];
        $mesg = json_encode($jarr);
        
        $amsg = Jmsg::unpack($mesg)::$amsg;
        // var_dump($amsg);exit;
        $this->assertSame($jarr['ns'], $amsg['ns']);        
        $this->assertSame($jarr['method'], $amsg['method']);
        $this->assertArrayHasKey('params', $amsg);        
        $this->assertArrayHasKey('nm', $amsg['params']);
        $this->assertEquals($jarr['params']['nm'], $amsg['params']['nm']);                        
    }

    function testRedat():void {
        $jarr = ['ns'=>'Pitk\Jmsg', 'method'=>'unpack', 'params'=>['nm'=>'crown', ], ];
        $mesg = json_encode($jarr);

        $rdat = ['succ'=>1, 'code'=>200, 'mesg'=>'success', 'data'=>['result'=>'abc',], ];
        $amsg = Jmsg::unpack($mesg)::redat($rdat)::$amsg;
        // var_dump($amsg);exit;
        $this->assertSame($jarr['ns'], $amsg['ns']);        
        $this->assertSame($jarr['method'], $amsg['method']);
        $this->assertArrayHasKey('params', $amsg);        
        $this->assertArrayHasKey('nm', $amsg['params']);
        $this->assertEquals($jarr['params']['nm'], $amsg['params']['nm']);

        $this->assertSame($rdat['succ'], $amsg['dat']['succ']);
        $this->assertSame($rdat['code'], $amsg['dat']['code']);
        $this->assertSame($rdat['mesg'], $amsg['dat']['mesg']);
        $this->assertSame($rdat['data']['result'], $amsg['dat']['data']['result']);                        
        
    }
    

    function testPack():void {
        $jarr = ['ns'=>'Pitk\Jmsg', 'method'=>'unpack', 'params'=>['nm'=>'crown', ], ];
        $rdat = ['succ'=>1, 'code'=>200, 'mesg'=>'success', 'data'=>['result'=>'abc',], ];
        
        $jmsg = Jmsg::pack($jarr['ns'], $jarr['method'], $jarr['params'], $rdat)::$jmsg;
        $amsg = json_decode($jmsg, 1);
        
        // var_dump($amsg);exit;
        $this->assertSame($jarr['ns'], $amsg['ns']);        
        $this->assertSame($jarr['method'], $amsg['method']);
        $this->assertArrayHasKey('params', $amsg);        
        $this->assertArrayHasKey('nm', $amsg['params']);
        $this->assertEquals($jarr['params']['nm'], $amsg['params']['nm']);

        $this->assertSame($rdat['succ'], $amsg['dat']['succ']);
        $this->assertSame($rdat['code'], $amsg['dat']['code']);
        $this->assertSame($rdat['mesg'], $amsg['dat']['mesg']);
        $this->assertSame($rdat['data']['result'], $amsg['dat']['data']['result']);                        
    }
    */
    function testMsger():void {
        $jarr = ['ns'=>'Pitk\Jmsg', 'method'=>'redat', 'params'=>['nm'=>'crown', ], ];
        $rdat = ['succ'=>1, 'code'=>200, 'mesg'=>'success', 'data'=>['result'=>'abc',], ];

        // $mesg = json_encode($jarr);        
        // $amsg = Jmsg::unpack($mesg)::msger()::$amsg;
        // // var_dump($amsg);exit;        
        // $dat_mesg = ' missing params:dat;';
        // $this->assertSame($amsg['dat']['mesg'], $dat_mesg);
        
        $jarr['params'] = ['dat'=>$rdat,];
        $mesg = json_encode($jarr);        
        $amsg = Jmsg::unpack($mesg)::msger(true,false)::$amsg;
        var_dump($amsg);exit;        
        
        $this->assertSame($jarr['ns'], $amsg['ns']);
        $this->assertSame($jarr['method'], $amsg['method']);
        $this->assertArrayHasKey('params', $amsg);        
        $this->assertArrayHasKey('dat', $amsg['params']);
        $this->assertEquals($jarr['params']['dat']['succ'], $amsg['params']['dat']['succ']);
        $this->assertEquals($jarr['params']['dat']['code'], $amsg['params']['dat']['code']);

        // test msger return
        $result = $jarr['ns']; // self::class
        $this->assertSame($result, $amsg['dat']['data']);                        
    }
    
    //cls.end
}
