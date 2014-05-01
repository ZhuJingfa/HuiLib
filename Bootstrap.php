<?php
namespace HuiLib;

use HuiLib\App\Front;

/**
 * 系统初始化引导文件，非单例模式
 * 
 * SYS_PATH 系统根目录，在库和应用目录上一级
 * LIB_PATH 库根目录，约定库目录在系统目录下
 * APP_PATH 应用根目录，约定应用目录在系统目录下
 * WWW_PATH 网页根目录
 * APP_ENV 当前应用执行环境，匹配相关配置
 * RUN_METHOD 应用执行方式web || bin
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Bootstrap
{
	//运行方式
	const RUN_BIN='Bin';
	const RUN_WEB='Web';
	
	//运行环境
	//产品环境
	const ENV_PRODUCTION='production';
	//演示环境
	const ENV_STAGING='staging';
	//测试环境
	const ENV_TESTING='testing';
	//开发环境
	const ENV_DEVELOPMENT='development';
	
	//默认环境
	const DEFAULT_ENV = 'production';
	
	/**
	 * 运行环境
	 * @var string Enum
	 */
	private $runEnv;
	
	/**
	 * 运行入口
	 * 
	 * 支持运行方式：web、bin
	 */
	private $runMethod;
	
	/**
	 * 应用单例
	 * @var \HuiLib\App\AppBase
	 */
	private $application;
	
	/**
	 * 期末执行绑定
	 * @var HuiLib\Runtime\ShutCall
	 */
	private $shutCall;
	
	private $allowedEnv = array (self::ENV_PRODUCTION, self::ENV_STAGING, self::ENV_TESTING, self::ENV_DEVELOPMENT );
	
	/**
	 * 加载器实例
	 * @var \HuiLib\Loader\AutoLoad
	 */
	private $loadInstance;
	
	private function __construct()
	{
		if (! defined ( 'RUN_METHOD' ) ) {
			throw new \Exception ( "Please define Constant var RUN_METHOD  in the entry!" );
		}
		$this->runMethod=RUN_METHOD;
		
		$this->initPath ();
		$this->initEnv ();
		$this->initLoader ();
	}

	/**
	 * 定义系统路径常量
	 * 
	 * @throws \Exception
	 */
	private function initPath()
	{
		define ( 'SEP', DIRECTORY_SEPARATOR );
		//URL地址分隔符
		define ( 'URL_SEP', '/' );
		//命名空间分隔符
		define ( 'NAME_SEP', '\\' );
		define ( 'LIB_PATH', dirname ( __FILE__ ) . SEP );
		define ( 'SYS_PATH', dirname ( LIB_PATH ) . SEP );
		
		if (! defined ( 'APP_PATH' ) || ! defined ( 'WWW_PATH' )) {
			throw new \Exception ( "Please define Constant var APP_PATH & WWW_PATH  in the entry!" );
		}
	}

	/**
	 * 引入注册自动加载类
	 */
	private function initLoader()
	{
		include_once LIB_PATH . 'Loader/AutoLoad.php';
		$this->loadInstance = \HuiLib\Loader\AutoLoad::getInstance ();
		spl_autoload_register ( array ($this->loadInstance, 'loadClass' ) );
		Front::getInstance()->setLoader($this->loadInstance);
	}
	
	public function autoLoaderInstance(){
		return self::$loadInstance;
	}

	/**
	 * 初始化应用
	 * @return \HuiLib\App\AppBase
	 */
	public function createApp($config)
	{
		$this->application=\HuiLib\App\AppBase::factory($this->runMethod, $config);
		Front::getInstance()->setApp($this->application);
		
		return $this->application;
	}

	/**
	 * 初始化应用当前运行环境
	 */
	private function initEnv()
	{
		if (isset ( $_SERVER ['SERVER_ENV'] ) && in_array ( $_SERVER ['SERVER_ENV'], $this->allowedEnv )) {
			define ( "APP_ENV", $_SERVER ['SERVER_ENV'] );
		} else {
			define ( "APP_ENV", self::DEFAULT_ENV );
		}
	}
	
	/**
	 * 获取允许的服务器环境配置
	 */
	public function getAllowEnv(){
		return $this->allowedEnv;
	}
	
	/**
	 * 获取引导类实例
	 * @return \HuiLib\Bootstrap
	 */
	public static function create()
	{
		$instance = new self ();
		Front::getInstance()->setBootstrap($instance);
		return $instance;
	}
}
