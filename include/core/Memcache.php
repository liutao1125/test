<?php

/*****************************************************************************
//
// PHP MemCached Client 
//
// Copyright ( C ) 2005  Dan Thrue, Weird Silence, www.weirdsilence.net
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or ( at your option ) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
*****************************************************************************/

define("WS_BUFFER", 1024);
define("WS_OBJECT", 1);
define("WS_COMPRESSED", 2);

class Memcache
{
	var $servers = array();
	var $sockets = array();
	var $debug = false;
	//var $compress = false;
	var $values = array();

	function Memcache($options=array())
	{
		$this->servers = $options['servers'];
		$this->debug = $options['debug'];
		//$this->compress = $options['compress'];
	}

	//?mc?
	function connect($host,$port)
	{
		$this->servers= is_array($this->servers)? ($this->servers+array("$host:$port")) : array("$host:$port") ;
		
		$sock = socket_create (AF_INET, SOCK_STREAM, getprotobyname("TCP"));

		if(!@socket_connect($sock, $host, $port))
		{
			$this->error = "Couldnt connect to server: $server";
			return false;
		}
		return $sock;
	}

	function set($key, $val, $compress=false, $expire=0)
	{
		$this->_sendCmd('set', $key, $val, $compress, $expire);
	}

	function get($key)
	{
		if (!isset($this->values[$key]))
		{
			$cmd = "get %s \r\n";
			$cmd = sprintf($cmd, $key);

			$sock = $this->_getCurrentSocket($key);
			if (!$this->_rawCmd($sock, $cmd))
			{
				return false;
			}
			$response = $this->_rawResponse($sock);
			$arr = $this->_clearArray(explode("\r\n", $response));
			if (!@list($header, $val, $dummy) = $arr)
			{
				$this->error = "Couldnt extract value";
				return false;
			}
			list($dummy, $key, $flags, $len) = explode(' ', $header);
			if (($flags & WS_COMPRESSED) == WS_COMPRESSED)
			{
				$val = gzuncompress($val);
			}
			if (($flags & WS_OBJECT) == WS_OBJECT)
			{
				$val = unserialize($val);
			}
			$this->values[$key] = $val;
		}
		return $this->values[$key];
	}

	function _clearArray($arr)
	{
		$ret = array();
		foreach ($arr as $val)
		{
			if (strlen($val) > 0) $ret[] = $val;
		}
		return $ret;
	}

	function _getCurrentSocket($key)
	{
		$index = 0;
		if (count($this->servers) > 1)
		{
			$index = $this->_hash($key) % count($this->servers);
		}
		if (!is_resource($this->sockets[$index]))
		{
			$sock = $this->_connect($this->servers[$index]);
			if (is_resource($sock))
			{
				$this->sockets[$index] = $sock;
			}
		}
		return $this->sockets[$index];
	}

	function _hash($key)
	{
		$val = 0;
		for ($i=1; $i<=5; $i++)
		{
			$val += ord(substr($key, -$i, 1));
		}
		return $val;
	}

	function _sendCmd($cmd, $key, $val, $compress, $expire)
	{
		$flags = 0;
		if(!is_scalar($val))
		{
			$val = serialize($val);
			$flags += WS_OBJECT;
		}
		//if ($this->compress)
		if ($compress)
		{
			$val = gzcompress($val);
			$flags += WS_COMPRESSED;
		}
		$raw = "%s %s %d %d %d \r\n%s\r\n";
		$raw = sprintf($raw, $cmd, $key, $flags, $expire, strlen($val), $val);

		$sock = $this->_getCurrentSocket($key);
		if (!$this->_rawCmd($sock, $raw))
		{
			return false;
		}
		$response = $this->_rawResponse($sock, 6, PHP_NORMAL_READ);
		if ($response == 'STORED')
		{
			return true;
		}
		else
		{
			$this->error = "Didnt get STORED from server";
			return false;
		}
	}

	function _rawCmd(&$sock, $cmd)
	{
		$len = strlen($cmd);
		$offset = 0;
		while ($res = socket_write($sock, substr($cmd, $offset, WS_BUFFER), WS_BUFFER))
		{
			if ($res === false) break;
			$offset += $res;
		}
		if ($offset < $len)
		{
			$this->error = "Failed to send raw command";
			return false;
		}
		return true;
	}

	function _rawResponse(&$sock, $len=0, $type=PHP_BINARY_READ)
	{
		if ($len > 0)
		{
			if ($buffer = socket_read($sock, $len, $type))
			{
				if ($buffer === false)
				{
					$this->error = "Failed to get response from server";
					return false;
				}
				return $buffer;
			}
			return '';
		}
		else
		{
			$buffer = '';
			while($buf = socket_read($sock, WS_BUFFER, $type))
			{
				if ($buf === false) break;
				$buffer .= $buf;
				if (strpos($buffer, "END\r\n")+5 == strlen($buffer)) break;
			}
			return $buffer;
		}
	}

	function _connect($server)
	{
		$arr = explode(':', $server);
		$host = $arr[0];
		$port = $arr[1];

		$sock = socket_create (AF_INET, SOCK_STREAM, getprotobyname("TCP"));

		if(!@socket_connect($sock, $host, $port))
		{
			$this->error = "Couldnt connect to server: $server";
			return false;
		}
		return $sock;
	}
	

	function disconnect()
	{
		foreach ($this->sockets as $sock)
		{
			socket_close($sock);
		}
	}

	function disconnect_all()
	{
		$this->disconnect();
	}
}

?>
