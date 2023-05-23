<?php

namespace Pitk\Tests;

use PHPUnit\Framework\TestCase;
use Pitk\Pitk;

class ValidateTest extends TestCase {
    protected $dbm; // Pitk 实例


    function setUp():void {
        $this->dbm = new Pitk();
     }


     function testValidate1():void {
        $rule = [
            'username'=>['required','alpha_numeric',],
            'password'=>['required','between_len'=>[6,16], ],
            ];
        $fltr = [
            'username'=>['trim','strtolower','sanitize_string',],
            'password'=>['trim', ],
        ];
        $emsg = [
            'username'=>['required'=>'必填用户姓名', 'alpha_numeric'=>'用户姓名仅可用字母数字组合', ],
            'password'=>['required'=>'必填用户密码', 'between_len'=>'用户密码长度仅限6至16位',],
        ];

        $data = [
            'username'=>'MyNameIs',
            'password'=>' 123456',
        ];

        $result = $this->dbm->validate($data, $rule, $fltr, $emsg);

        $this->assertSame(1, $result[0]);
        $this->assertSame(strtolower($data['username']), $result[1]['username']);
        $this->assertSame(trim($data['password']), $result[1]['password']);

    }

    function testValidate2():void {
        $rule = [
            'username'=>['required','alpha_numeric',],
            'password'=>['required','between_len'=>[6,16], ],
            ];
        $fltr = [
            'username'=>['trim','strtolower','sanitize_string',],
            'password'=>['trim', ],
        ];
        $emsg = [
            'username'=>['required'=>'必填用户姓名', 'alpha_numeric'=>'用户姓名仅可用字母数字组合', ],
            'password'=>['required'=>'必填用户密码', 'between_len'=>'用户密码长度仅限6至16位',],
        ];

        $data = [
            'username'=>'MyNameIs#@!',
            'password'=>' 123456',
        ];

        $result = $this->dbm->validate($data, $rule, $fltr, $emsg);

        $this->assertSame(0, $result[0]);
        $this->assertFalse($result[1]); // invalid result[1]

        $this->assertSame($emsg['username']['alpha_numeric'], $result[2]['username']);

    }

    //cls.end
}
