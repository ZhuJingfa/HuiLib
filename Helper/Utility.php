<?php
namespace HuiLib\Helper;

/**
 * 通用函数库
 * 
 * 其他没地方放的函数，放这里
 *
 * @author 祝景法
 * @since 2013/09/20
 */
class Utility
{

	/**
	 * 随机生成固定长度的随机串
	 * 
	 * @param int $length 随机串长度
	 */
	public static function geneRandomHash($length = 40)
	{
		//生成40位字母和数字组成的随机串
		$charList = array ();
		//0-9
		for($iter = ord ( '0' ); $iter <= ord ( '9' ); $iter ++) {
			$charList [] = chr ( $iter );
		}
		//a-z
		for($iter = ord ( 'a' ); $iter <= ord ( 'z' ); $iter ++) {
			$charList [] = chr ( $iter );
		}
		
		//不用mt_strand，播种了反而在高并发下一致了
		$result = array ();
		$charCount = count ( $charList );
		for($iter = 0; $iter < $length; $iter ++) {
			$result [] = $charList [mt_rand ( 0, mt_rand(5,600) ) % $charCount];
		}
		
		return implode ( '', $result );
	}

	/**
	 * 将IP转换为数字按255基础换算。
	 * 
	 *  经测试，php数值可以超过2^32，这里的最大值也小于该值。
	 *  
	 * @param $ip string 四位数字组成的IP
	 * @return 某ip的对应唯一值，忽略没有意义的前缀0位
	 */
	public static function ipToNum($ip)
	{
		if (empty ( $ip ))
			return 0;
		$ipArray = self::splitIpString ( $ip );
		
		return $ipArray ['ip1'] * 255 * 255 * 255 + $ipArray ['ip2'] * 255 * 255 + $ipArray ['ip3'] * 255 + $ipArray ['ip4'];
	}

	/**
	 * 分拆IP
	 * 
	 * param $ip string 四位数字组成的IP
	 */
	public static function splitIpString($ip)
	{
		if (empty ( $ip ))
			return false;
		
		$ipNew = array ();
		list ( $ipNew ['ip1'], $ipNew ['ip2'], $ipNew ['ip3'], $ipNew ['ip4'] ) = @explode ( '.', $ip );
		$ipNew = array_map ( 'intval', $ipNew );
		$ipArray [$ip] = $ipNew;
		
		return $ipNew;
	}

	/**
	 * 加密函数
	 * 
	 * @param string $string 待加密字符串
	 * @param string $operation 加密或解密 默认DECODE解密
	 * @param int $expiry 过期时间
	 * @param string $key 混淆密钥
	 */
	public static function authcode($string, $operation = 'DECODE', $expiry = 0, $key = 'zjsu%%elisui_iyunlin_zjgdx@@@zhfsf#gn')
	{
		$ckeyLength = 4;
		$key = md5 ( $key );
		$keya = md5 ( substr ( $key, 0, 16 ) );
		$keyb = md5 ( substr ( $key, 16, 16 ) );
		$keyc = $ckeyLength ? ($operation == 'DECODE' ? substr ( $string, 0, $ckeyLength ) : substr ( md5 ( microtime () ), - $ckeyLength )) : '';
		
		$cryptkey = $keya . md5 ( $keya . $keyc );
		$key_length = strlen ( $cryptkey ); // 16+32
		

		$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckeyLength ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
		$stringLength = strlen ( $string );
		
		$result = '';
		$box = range ( 0, 255 ); // 建立包含1到255的数组
		

		$rndkey = array ();
		for($i = 0; $i <= 255; $i ++) {
			$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
		}
		
		for($j = $i = 0; $i < 256; $i ++) {
			$j = ($j + $box [$i] + $rndkey [$i]) % 256;
			// 交换$box [$i],$box [$j]的值
			$tmp = $box [$i];
			$box [$i] = $box [$j];
			$box [$j] = $tmp;
		}
		
		for($a = $j = $i = 0; $i < $stringLength; $i ++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box [$a]) % 256;
			// 交换$box [$a],$box [$j]的值
			$tmp = $box [$a];
			$box [$a] = $box [$j];
			$box [$j] = $tmp;
			// 异或运算
			$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
		}
		
		if ($operation == 'DECODE') {
			if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
				return substr ( $result, 26 );
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
		}
	}
	
	/**
	 * 密码关键信息隐藏，隐藏中间的密码
	 * 
	 * @param string $password 密码信息
	 */
	public static function censorPassword($password) {
		if (empty ( $password ))
			return false;
	
		$length = \HuiLib\Helper\String::strlen( $password );
		if ($length < 6)
			return '******';
	
		return \HuiLib\Helper\String::substr( $password, 0, 2 ) . str_repeat ( '*', $length > 10 ? 6 : $length - 4 ) . \HuiLib\Helper\String::substr ( $password, - 2, 2 );
	}
	
	/**
	 * 生成UUID 
	 * 
	 * 来自http://stackoverflow.com/questions/2040240
	 */
	public static function genUuid() {
	    $uuid = array(
	            'time_low'  => 0,
	            'time_mid'  => 0,
	            'time_hi'  => 0,
	            'clock_seq_hi' => 0,
	            'clock_seq_low' => 0,
	            'node'   => array()
	    );
	
	    $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
	    $uuid['time_mid'] = mt_rand(0, 0xffff);
	    $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
	    $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
	    $uuid['clock_seq_low'] = mt_rand(0, 255);
	
	    for ($i = 0; $i < 6; $i++) {
	        $uuid['node'][$i] = mt_rand(0, 255);
	    }
	
	    $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
	            $uuid['time_low'],
	            $uuid['time_mid'],
	            $uuid['time_hi'],
	            $uuid['clock_seq_hi'],
	            $uuid['clock_seq_low'],
	            $uuid['node'][0],
	            $uuid['node'][1],
	            $uuid['node'][2],
	            $uuid['node'][3],
	            $uuid['node'][4],
	            $uuid['node'][5]
	    );
	
	    return $uuid;
	}
}