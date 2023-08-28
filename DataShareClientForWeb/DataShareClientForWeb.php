<?php

class DataShareClient extends Client
{


    public function push($key,$value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd'   => 'push',
            'key'   => $key,
            'value' => $value,
        ), $connection);
        $this->readFromRemote($connection);
    }

    public function pop($key,$right = true)
{
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'pop',
            'key' => $key,
            'right' => $right,

        ), $connection);

        return $this->readFromRemote($connection);
    }

    public function bpop($key,$right = true)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'bpop',
            'key' => $key,
            'right' => $right,

        ), $connection);

        return $this->readFromRemote($connection);
    }

    public function watch($key,$wait =  0)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'get',
            'key' => $key,
            'wait' => $wait,
        ), $connection);
        return $this->readFromRemote($connection);
    }
    public function expire($key,$expire_time)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd'   => 'expire',
            'key'   => $key,
            'value' => $expire_time<(2592000)?(microtime(1)+$expire_time):$expire_time,
        ), $connection);
        $this->readFromRemote($connection);
    }

    public function getConnections()
    {
        $c  = count($this->_globalServers);
        $connections = [];
        for($i = 0;$i<$c;$i++){
            $connection = stream_socket_client("tcp://{$this->_globalServers[$i]}", $code, $msg, $this->timeout);
            if(!$connection)
            {
                throw new \Exception($msg);
            }
            stream_set_timeout($connection, $this->timeout);
            $connections[$i] = $connection;
        }
        return $connections;
    }

    public function keys($key = '*')
    {
        $ret_arr = [];
        $connections = $this->getConnections();
        foreach ($connections as $connection){
            $this->writeToRemote(array(
                'cmd'   => 'keys',
                'key'   => $key,
            ), $connection);
            $ret =  $this->readFromRemote($connection);
            if(!empty($ret)){
                $ret_arr = array_merge($ret_arr,$ret);
            }
        }
        return $ret_arr;
    }

}
class Client
{
    /**
     * Timeout.
     * @var int
     */
    public $timeout = 5;

    /**
     * Heartbeat interval.
     * @var int
     */
    public $pingInterval = 25;

    /**
     * Global data server address.
     * @var array
     */
    protected $_globalServers = array();

    /**
     * Connection to global server.
     * @var resource
     */
    protected $_globalConnections = null;

    /**
     * Cache.
     * @var array
     */
    protected $_cache = array();

    /**
     * Construct.
     * @param array/string $servers
     */
    public function __construct($servers)
    {
        if(empty($servers))
        {
            throw new \Exception('servers empty');
        }
        $this->_globalServers = array_values((array)$servers);
    }

    /**
     * Connect to global server.
     * @throws \Exception
     */
    protected function getConnection($key)
    {
        $offset = crc32($key)%count($this->_globalServers);
        if($offset < 0)
        {
            $offset = -$offset;
        }

        if(!isset($this->_globalConnections[$offset]) || !is_resource($this->_globalConnections[$offset]) || feof($this->_globalConnections[$offset]))
        {
            $connection = stream_socket_client("tcp://{$this->_globalServers[$offset]}", $code, $msg, $this->timeout);
            if(!$connection)
            {
                throw new \Exception($msg);
            }
            $raw_socket = \socket_import_stream($connection);
            \socket_set_option($raw_socket, \SOL_SOCKET, \SO_KEEPALIVE, 1);
            \socket_set_option($raw_socket, \SOL_TCP, \TCP_NODELAY, 1);
            stream_set_timeout($connection, $this->timeout);
            if(class_exists('\Workerman\Lib\Timer') && php_sapi_name() === 'cli')
            {
                $timer_id = \Workerman\Lib\Timer::add($this->pingInterval, function($connection)use(&$timer_id)
                {
                    $buffer = pack('N', 8)."ping";
                    if(strlen($buffer) !== @fwrite($connection, $buffer))
                    {
                        @fclose($connection);
                        \Workerman\Lib\Timer::del($timer_id);
                    }
                }, array($connection));
            }
            $this->_globalConnections[$offset] = $connection;
        }
        return $this->_globalConnections[$offset];
    }


    /**
     * Magic methods __set.
     * @param string $key
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd'   => 'set',
            'key'   => $key,
            'value' => $value,
        ), $connection);
        $this->readFromRemote($connection);
    }

    /**
     * Magic methods __isset.
     * @param string $key
     */
    public function __isset($key)
    {
        return null !== $this->__get($key);
    }

    /**
     * Magic methods __unset.
     * @param string $key
     * @throws \Exception
     */
    public function __unset($key)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'delete',
            'key' => $key
        ), $connection);
        $this->readFromRemote($connection);
    }

    /**
     * Magic methods __get.
     * @param string $key
     * @throws \Exception
     */
    public function __get($key)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'get',
            'key' => $key,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Cas.
     * @param string $key
     * @param mixed $value
     * @throws \Exception
     */
    public function cas($key, $old_value, $new_value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd'     => 'cas',
            'md5' => md5(serialize($old_value)),
            'key'     => $key,
            'value'   => $new_value,
        ),$connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Add.
     * @param string $key
     * @throws \Exception
     */
    public function add($key, $value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'add',
            'key' => $key,
            'value' => $value,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Increment.
     * @param string $key
     * @throws \Exception
     */
    public function increment($key, $step = 1)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote(array(
            'cmd' => 'increment',
            'key' => $key,
            'step' => $step,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Write data to global server.
     * @param string $buffer
     */
    protected function writeToRemote($data, $connection)
    {
        $buffer = serialize($data);
        $buffer = pack('N',4 + strlen($buffer)) . $buffer;
        $len = fwrite($connection, $buffer);
        if($len !== strlen($buffer))
        {
            throw new \Exception('writeToRemote fail');
        }
    }

    /**
     * Read data from global server.
     * @throws Exception
     */
    protected function readFromRemote($connection)
    {
        $all_buffer = '';
        $total_len = 4;
        $head_read = false;
        while(1)
        {
            $buffer = fread($connection, 8192);
            if($buffer === '' || $buffer === false)
            {
                throw new \Exception('readFromRemote fail');
            }
            $all_buffer .= $buffer;
            $recv_len = strlen($all_buffer);
            if($recv_len >= $total_len)
            {
                if($head_read)
                {
                    break;
                }
                $unpack_data = unpack('Ntotal_length', $all_buffer);
                $total_len = $unpack_data['total_length'];
                if($recv_len >= $total_len)
                {
                    break;
                }
                $head_read = true;
            }
        }
        return unserialize(substr($all_buffer, 4));
    }
}
