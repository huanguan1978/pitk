<?php
namespace Pitk;
/**
  Jmsg，ＷebSocket长连接信息包结构
*/


class Jmsg {

    /**
     * 静态类成员,关联数组版消息体
     *
     * @access public
     * @var array $amsg 
     */    
    static array $amsg = ['ns'=>'', 'method'=>'', 'time'=>0, 'params'=>[],
                          'dat'=>['time'=>0, 'succ'=>0, 'code'=>0, 'mesg'=>'', 'data'=>[],
                                  '_unpack_jmsg'=>'', '_msger_mesg'=>[], '_msger_real_arg'=>[], '_msger_miss_arg'=>[], 
                                  '_err'=>['throwable'=>'', 'debug_backtrace'=>'', 'error_get_last'=>'', ],
                          ],
    ];

    /**
     * 静态类成员,JSON字符串版消息体
     *
     * @access public
     * @var string $jmsg
     */    
    static string $jmsg = '';

    /**
      回显数据重置, 若需自返回数据项及值时可用此函数处理
      @param array  $data 需要重置的数据
      @return self::class 类自已
    */
    
    static function redat(array $dat) {
        $upd = 0;
        if(isset($dat['_err']) && is_array($dat['_err'])){
            $amsg_err_key = array_keys(self::$amsg['dat']['_err']);            
            foreach($amsg_err_key as $k){
                if(isset($dat['_err'][$k])){
                    self::$amsg['dat']['_err'][$k] = $dat['_err'][$k];
                    $upd =1;
                }
            }
            unset($dat['_err']);
        }
        
        foreach($dat as $k=>$v){
            if(isset(self::$amsg['dat'][$k])){
                self::$amsg['dat'][$k] = $v;
                $upd =1;
            }
        }

        if($upd){
            self::pack(self::$amsg['ns'], self::$amsg['method'], self::$amsg['params'], self::$amsg['dat']);
        }

        return self::class;
    }

    /**
      封包消息
      @param string $ns   调用命名空间(空间和类名)
      @param string $name 调用方法名字
      @param array  $parm 调用方法时传递的参数            
      @param array  $dat 调用完成后自定返回结果数据
      @return self::class 类自已
    */
    
    static function pack(string $ns, string $method, array $params=[], array $dat=[]) {
        $item = ['ns'=>$ns, 'method'=>$method, 'params'=>$params, ];
        if(empty($item['time'])){
            $item['time']=time();
        }
        if($dat){
            $dat['time'] = time(); $item['dat'] = $dat;
        }
        self::$amsg =array_merge(self::$amsg, $item);
        self::$jmsg = json_encode(self::$amsg, JSON_UNESCAPED_UNICODE);
        return self::class;
    }

    /**
      解包消息
      @param string $jmsg 消息正文(JSON格式消息正文)
      @return object 类自已
    */
    
    static function unpack(string $jmsg) {
        self::$amsg['dat']['_unpack_jmsg'] .= $jmsg;
        $jdat = json_decode($jmsg, 1);
        if( (JSON_ERROR_NONE === json_last_error())
           && (isset($jdat['ns']) && $jdat['ns'])
           && (isset($jdat['method']) && $jdat['method'] )
           && (isset($jdat['params']) && is_array($jdat['params']))
        ){
            self::pack($jdat['ns'], $jdat['method'], $jdat['params']);
        }else{
            self::$amsg['dat']['mesg'].=' invalid jsondata, JSON_ERROR;';
        }
        return self::class;
    }

    /**
      执行消息,执行完成后的结果可通过类变量获得
      @param ?bool $dbginfo 是否输出调试信息
      @param ?bool $syslog  是否日志记录调试信息      
      @return object 类自已
    */
    
    static function msger(?bool $dbginfo=true, ?bool $syslog=false) {
        $ns = self::$amsg['ns']; $method=self::$amsg['method']; $params = self::$amsg['params'];
        $dat = []; $mesg = []; 
        $result = null;
        if(class_exists($ns) && method_exists($ns, $method) && is_array($params) ){

            $miss = $rwarg = []; 
            $rc = new \ReflectionClass($ns); $rm = $rc->getMethod($method);
            foreach ($rm->getParameters() as $i => $param) {
                $pname = $param->getName();
                if ($param->isPassedByReference()) {
                    /// @todo shall we raise some warning?
                }
                if (array_key_exists($pname, $params)) {
                    $rwarg[] = $params[$pname];
                }else if ($param->isDefaultValueAvailable()) {
                    $rwarg[] = $param->getDefaultValue();
                }else {
                    $miss[] = $pname;
                }
            }

            if($miss){
                $mesg[] = sprintf('missing params:%s', implode(',', $miss) );
                $dat['_msger_miss_arg'] = $miss;
            }else{
                $dat['_msger_real_arg'] = $rwarg;
                error_clear_last();
                try{
                    $result = call_user_func_array([$ns, $method], $rwarg);
                    $dat['data'] = $result;
                }catch(Throwable $t){
                    $_err = [
                        'throwable'=> $t->__tostring(),
                        'debug_backtrace' => debug_backtrace(),
                        'error_get_last' => error_get_last(),
                    ];
                    $dat['_err'] = $_err;
                }
                if(empty($dat['_err']['error_get_last'])){ 
                    $dat['succ'] = 1;
                }
            }
            
        }else{
            if(!class_exists($ns)){                $mesg[] = 'not exists class';
            }
            if(method_exists($ns, $method)){       $mesg[] = 'not exists method';
            }
            if(! is_array($params)){               $mesg[] = 'invalud params';
            }
        }

        $dat['time'] = time();
        $dat['_msger_mesg'] = implode(';', $mesg); 
        if($syslog){
            if(openlog(__METHOD__, LOG_PID|LOG_PERROR, LOG_USER)){
                syslog(LOG_DEBUG, json_encode($dat, JSON_UNESCAPED_UNICODE));
                closelog();
            }
        }

        if(!$dbginfo){
            foreach($dat as $k=>$v){
                if(false!==strpos($k,'_')){
                    unset($dat[$k]);
                }
            }
        }

        self::$amsg['dat']  = array_merge(self::$amsg['dat'], $dat);
        return self::class;        
    }


    /**
      服务端长连接消息
      @param callbale $datasource 产生数据的回调函数,此回调函数需要有一个可选入参id,退回结果为id和data.
      @param ?int      $retry      重试时间,默认10毫秒
      @param ?string   $event      自定义事件名,默认为message触发H5端OnMessage监听            
      @return string H5数据(含HTTP头信息)
    */
    
    static function ssemsger(callbale $datasource, ?int $retry=10000, ?string $event=''):string {
        $headers = ['Content-Type: text/event-stream', 'Cache-Control: no-cache', 'Connection: keep-alive', 'Access-Control-Allow-Origin: *', ];
        foreach($headers as $header){            header($header);
        }

		$counter = rand(1, 10);
        $ebody = ['event'=>$event, 'id'=>'', 'data'=>'', 'retry'=>$retry, ];
		while(1){
			$curDate = date(DATE_ISO8601);
            $jdat = json_encode(['time'=>$curDate,]);
            printf("event: ping\nretry: %s\ndata: %s\n\n", $retry, $jdat);
			//echo "event: ping\n", 'data: {"time": "' . $curDate . '"}', "\n\n";


			$counter--; 			// Send a simple message at random intervals.
			if (!$counter) {
				$counter = rand(1, 10);
                $lastid = isset($_SERVER["HTTP_LAST_EVENT_ID"])?$_SERVER["HTTP_LAST_EVENT_ID"]:'';
                try{
                    $ds = $datasource($lastid);
                    if($ds && isset($ds['id']) && isset($ds['data']) && $ds['id'] && $ds['data'] && is_string($ds['id']) && is_string($ds['data']) ){
                        $ebody['id'] = $ds['id']; $ebody['data'] = $ds['data'];
                    }
                }catch(Throwable $t){
                    $ebody['event']='error'; $ebody['id'] = $ds['id']; $ebody['data'] = $t->__tostring();
                }
            
                
				// echo 'data: This is a message at time ' . $curDate, "\n\n";
                if(isset($ebody['event']) && $ebody['event']){                    printf("event: %s\n", $ebody['event']);
                }
                if(isset($ebody['id']) && $ebody['id']){                    printf("id: %s\n", $ebody['id']);
                }
                if(isset($ebody['retry']) && $ebody['retry']){                    printf("retry: %s\n", $ebody['retry']);
                }
                $jdat = json_encode(['time'=>$curDate, 'data'=>'12345678', ]);
                printf("data: %s\n\n", $jdat);

			}

			while(ob_get_level() > 0) {
				ob_end_flush();
			}
			flush();
            
			if(connection_aborted()){ break;
			}
			sleep(1);
		}
        
    }
    
    //end.cls    
}
