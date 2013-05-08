<?php
/**
 * Socket class is used to send and receive data through sockets.  It runs non-blocking sockets which seem to work better
 * @author Mark Hobson
 */
class Socket {

	/**
	 * Binds an ip address
	 * @param string $ip_address
	 */
	static function bindAddress($ip_address) {
		// Bind the source addres
		$cmd = 'ifconfig eth0:' . sprintf("%u", ip2long($ip_address)) . ' ' . $ip_address . ' netmask 255.255.255.0';
		$ret_val = @shell_exec($cmd);
		$cmd = 'arping -b ' . $ip_address . ' -c 1';
		@shell_exec($cmd);
		return $ret_val;
	}
	
	/**
	 * Unbinds an ip address
	 * @param string $ip_address
	 */
	static function unBindAddress($ip_address) {
		$cmd = 'ifconfig eth0:' . sprintf("%u", ip2long($ip_address)) . ' down';
		return @shell_exec($cmd);
	}
		
	/**
	 * Creates a new socket
	 * @return resource
	 */
	static function createSocket() {
		return socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	}
	
	/**
	 * Binds a socket to an ip address
	 * @param string $ip_address
	 */
	static function bindSocket($sock, $ip_address) {
		// Bind the source addres
		return @socket_bind($sock, $ip_address);
	}
	
	/**
	 * Creates a new socket
	 * @return resource
	 */
	static function closeSocket($sock) {
		$ret_val = @socket_shutdown($sock, 2);
		// Socket close doesn't return anything, so we return the result of socket_shutdown (boolean)
		@socket_close($sock);
		return $ret_val;
	}
	
	/**
	 * Connects a socket to an external host and port
	 * @param string $ip_address
	 * @return boolean
	 */
	static function connectSocket($sock, $host, $port, &$connect_error = '', $socket_timeout = 5) {
		$attempts = 0;
		socket_set_block($sock);
		while (!($connected = @socket_connect($sock, $host, $port)) && $attempts++ < $socket_timeout) {
			$sock_error = socket_last_error();
			if ($sock_error != SOCKET_EINPROGRESS && $sock_error != SOCKET_EALREADY) {
				$connect_error = socket_strerror($sock_error);
				return false;
			}
			sleep(125);
		} 
		
		$connect_buffer = '';
		$attempts = 0;
		while (!($connect_buffer = @socket_read($sock, 4096, PHP_BINARY_READ)) && ($attempts++ < $socket_timeout)) {
			$sock_error = socket_last_error();
			if ($sock_error != SOCKET_EINPROGRESS && $sock_error != SOCKET_EALREADY && $sock_error != SOCKET_EAGAIN) {
				$connect_buffer .= socket_strerror($sock_error);
				return false;
			}
			usleep(125);
		}
		if (!$connected) {
			$connect_error = socket_strerror($sock_error);
			return false;
		}
		return $connect_buffer;
	}
	
	/**
	 * Writes to a socket and returns the result
	 * @param string $message
	 */
	static function writeSocket($sock, $message, &$buffer, $socket_timeout = 5) {
		socket_set_block($sock);
		$attempts = 0;
		while (!(@socket_write($sock, $message)) && ($attempts++ < $socket_timeout)) {
			$sock_error = socket_last_error();
			if ($sock_error != SOCKET_EINPROGRESS && $sock_error != SOCKET_EALREADY) {
				$buffer = socket_strerror($sock_error); 
				return false;
			}
			usleep(125);
		}
		$attempts = 0;
		while (!($buffer = @socket_read($sock, 4096, PHP_BINARY_READ)) && ($attempts++ < $socket_timeout)) {
			$sock_error = socket_last_error();
			if ($sock_error != SOCKET_EINPROGRESS && $sock_error != SOCKET_EALREADY && $sock_error != SOCKET_EAGAIN) {
				$buffer .= socket_strerror($sock_error);
				return false;
			}
			usleep(125);
		}
		return true;
	}
	
	
}
?>