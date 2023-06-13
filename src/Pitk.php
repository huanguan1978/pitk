<?php
/**
  Pitk，日常工作常用精选工具库，
  封装一些简洁高效的第三方工具库
*/

namespace Pitk;

use GUMP;
use Sabre\Cache\Apcu;
use Sabre\Cache\Memcached;

class Pitk {

    /**
      APCU缓存
      @param string $ns 缓存K的前缀
      @param integer $ttl 缓存时间，单位为秒，默认7200秒即2小时，0秒则为永不过期
      @return object 类实例，实现PSR16标准的类实列，可用PSR16方法调用
    */

    function apcucache(){
        $cache = new \Sabre\Cache\Apcu();
        return $cache;
    }

    function memdcache(array $servers=[['127.0.0.1','11211'],] ){
        $memcached = new \Memcached();
        foreach($servers as $srv){
            $memcached->addServer($srv[0], $srv[1]);
        }
        $cache = new \Sabre\Cache\Memcached($memcached);
        return $cache;
    }

    /**
      数据校验器
      @param string $data 要校验的数据
      @param string $rule 校验规则集
      @param string $fliter 数据过滤集
      @param string $emsg 自定校验错误显示信息
      @return array 校验结果,如[0, $data, $emsg], 索引1值0有错1无值，索引2处理过的数据，索引3出错清单
    */
    function validate(array $data, array $rule, array $fliter=[], array $emsg=[] ):array {
        $gump = new GUMP();
        $gump->validation_rules($rule);
        $gump->set_fields_error_messages($emsg);
        $gump->filter_rules($fliter);
        $data = $gump->run($data);

        $result = [0, $data, $emsg];
        if($gump->errors()){
            $result[2] = $gump->get_errors_array();
        }else{ // successs
            $result[0]=1;
            $result[2] = [];
        }

        return $result;
    }


    // cls.end
}
