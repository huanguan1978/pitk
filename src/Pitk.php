<?php
/**
  Pitk���ճ��������þ�ѡ���߿⣬
  ��װһЩ����Ч�ĵ��������߿�
*/

namespace Pitk;

use GUMP;
use Sabre\Cache\Apcu;
use Sabre\Cache\Memcached;

class Pitk {

    /**
      APCU����
      @param string $ns ����K��ǰ׺
      @param integer $ttl ����ʱ�䣬��λΪ�룬Ĭ��7200�뼴2Сʱ��0����Ϊ��������
      @return object ��ʵ����ʵ��PSR16��׼����ʵ�У�����PSR16��������
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
      ����У����
      @param string $data ҪУ�������
      @param string $rule У�����
      @param string $fliter ���ݹ��˼�
      @param string $emsg �Զ�У�������ʾ��Ϣ
      @return array У����,��[0, $data, $emsg], ����1ֵ0�д�1��ֵ������2����������ݣ�����3�����嵥
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
