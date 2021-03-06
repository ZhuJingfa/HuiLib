<?php
namespace HuiLib\Db;

use HuiLib\Db\Query;
use HuiLib\Db\Query\Where;

/**
 * 表数据抽象类
 * 
 * 从应用中表共用基类转换而来
 *
 * @author 祝景法
 * @since 2013/10/20
 */
class TableAbstract extends \HuiLib\Model\ModelBase
{
	/**
	 * 表名
	 * @var string
	 */
	const TABLE=NULL;
	
	/**
	 * 表行类名
	 * @var string
	 */
	const ROW_CLASS='\HuiLib\Db\RowAbstract';
	
	/**
	 * 存在相同主键行是否覆盖
	 */
	const CREATE_DUPLICATE=TRUE;//覆盖
	const CREATE_NO_DUPLICATE=FALSE;//不覆盖
	
	/**
	 * 事务更新锁定模式
	 *
	 * @var boolean
	 */
	protected $forUpdate = false;

	/**
	 * 获取表的Select对象
	 *
	 * @return \HuiLib\Db\Query\Select
	 */
	public function select()
	{
		return Query::select ( static::TABLE );
	}
	
	/**
	 * 通过主键获取单条记录
	 *
	 * @param string $value
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function getRowByPrimaryId($value)
	{
	    $select=Query::select ( static::TABLE );
	    if ($this->dbAdapter!==NULL) {
	        $select->setAdapter($this->dbAdapter);
	    }
	    if ($this->forUpdate) {
	        $select->enableForUpdate();
	    }
	    $rowClass=$this->getRowClass();
	    return $this->rowObject($select->where ( Where::createPair ( $rowClass::PRIMAY_IDKEY, $value ) )->limit ( 1 )->query ()->fetch ());
	}
	
	/**
	 * 通过主键获取列表记录
	 *
	 * @param string $ids 主键ID
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function getListByPrimaryIds($ids)
	{
	    $select=Query::select ( static::TABLE );
	    if ($this->dbAdapter!==NULL) {
	        $select->setAdapter($this->dbAdapter);
	    }
	    if ($this->forUpdate) {
	        $select->enableForUpdate();
	    }
	    $rowClass=$this->getRowClass();
	    return $this->rowSetObject($select->where ( Where::createQuote ( $rowClass::PRIMAY_IDKEY . ' in (?) ', $ids ) ));
	}
	
	/**
	 * 通过单个Field获取单条记录
	 * 
	 * @param string $field
	 * @param string $value
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function getRowByField($field, $value)
	{
		$select=Query::select ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$select->setAdapter($this->dbAdapter);
		}
		if ($this->forUpdate) {
			$select->enableForUpdate();
		}

		return $this->rowObject($select->where ( Where::createPair ( $field, $value ) )->limit ( 1 )->query ()->fetch ());
	}

	/**
	 * 通过单个Field获取多条记录
	 *
	 * @param string $field
	 * @param string $value
	 * @param int $limit 取多少条
	 * @param int $offset 从第几条开始
	 */
	public function getListByField($field, $value, $limit, $offset = 0)
	{
		$select=Query::select ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$select->setAdapter($this->dbAdapter);
		}
		if ($this->forUpdate) {
		    $select->enableForUpdate();
		}
		return $this->rowSetObject($select->where ( Where::createPair ( $field, $value ) )->limit ( $limit )->offset ( $offset ));
	}
	
	/**
	 * 通过单个Field的多个IDS获取多条记录
	 * 
	 * 通过where in实现
	 *
	 * @param string $field
	 * @param string $ids
	 */
	public function getListByIds($field, $ids)
	{
		$select=Query::select ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$select->setAdapter($this->dbAdapter);
		}
		if ($this->forUpdate) {
		    $select->enableForUpdate();
		}
		return $this->rowSetObject($select->where ( Where::createQuote ( $field . ' in (?) ', $ids ) ));
	}

	/**
	 * 通过单个Field获取单条记录的某个字段值
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $column 要获取的字段，是字段名，不是column序号
	 */
	public function getColumnByField($field, $value, $column)
	{
		$select=Query::select ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$select->setAdapter($this->dbAdapter);
		}
		$unit = $select->where ( Where::createPair ( $field, $value ) )->limit ( 1 )->query ()->fetch ();
		if (isset ( $unit [$column] )) {
			return $unit [$column];
		} else {
			return false;
		}
	}

	/**
	 * 通过关联数据插入某表一行数据
	 *
	 * @param array $setArray 插入数组
	 */
	public function insert($setArray)
	{
		$insert=Query::insert ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$insert->setAdapter($this->dbAdapter);
		}
		return $insert->kvInsert($setArray)->query();
	}
	
	/**
	 * 通过关联数据插入某表一行数据
	 *
	 * @param array $setArray 插入数组
	 */
	public function update($setArray, \HuiLib\Db\Query\Where $where)
	{
		$update=Query::update ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$update->setAdapter($this->dbAdapter);
		}
		return $update->sets($setArray)->where($where)->query();
	}
	
	/**
	 * 通过关联数据插入某表一行数据
	 *
	 * @param array $dataArray 插入数组
	 */
	public function delete(\HuiLib\Db\Query\Where $where)
	{
		$delete=Query::delete ( static::TABLE );
		if ($this->dbAdapter!==NULL) {
			$delete->setAdapter($this->dbAdapter);
		}
		return $delete->where($where)->query();
	}
	
	/**
	 * 创建新的一行
	 *
	 * @param boolean $duplicate 创建是否覆盖相同主键的值
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function createRow($duplicate=self::CREATE_NO_DUPLICATE)
	{
		$rowClass=static::ROW_CLASS;
		$rowInstance=$rowClass::createNewRow();
		$rowInstance->setTable($this);
		if ($duplicate) {
			$rowInstance->enableDupliateCreate();
		}
		return $rowInstance;
	}
	
	/**
	 * 返回行数据对象
	 * 
	 * @param array $data 结果数据
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function rowObject($data)
	{
		if ($data===FALSE) {
			return NULL;
		}
		
		$rowClass=static::ROW_CLASS;
		$rowInstance=$rowClass::create($data);
		$rowInstance->setTable($this);
		return $rowInstance;
	}
	
	/**
	 * 返回行列表数据对象
	 * 
	 * @param \HuiLib\Db\Query\Select $select
	 */
	public function rowSetObject($select)
	{
		if (!$select instanceof \HuiLib\Db\Query\Select) {
			return NULL;
		}

		$rowSetInstance=\HuiLib\Db\RowSet::create();
		$rowSetInstance->initBySelect($select);
		$rowSetInstance->setRowClass(static::ROW_CLASS);
		$rowSetInstance->setTable($this);
		return $rowSetInstance;
	}
	
	/**
	 * 返回行列表数据对象
	 *
	 * @param array $dataList 数据列表
	 */
	public function rowSetObjectByData($dataList)
	{
	    if (empty($dataList)) {
	        return NULL;
	    }
	
	    $rowSetInstance=\HuiLib\Db\RowSet::create();
	    $rowSetInstance->initByDataList($dataList);
	    $rowSetInstance->setRowClass(static::ROW_CLASS);
	    $rowSetInstance->setTable($this);
	    return $rowSetInstance;
	}
	
	/**
	 * 获取表行默认初始化数据
	 * @return array
	 */
	public static function getRowInitData()
	{
		$rowClass=static::ROW_CLASS;
		return $rowClass::getInitData();
	}
	
	/**
	 * 获取表关联的行类
	 * @return array
	 */
	public static function getRowClass()
	{
		return static::ROW_CLASS;
	}
	
	/**
	 * 开启更新锁定事务模式
	 */
	public function enableForUpdate()
	{
		$this->forUpdate=true;
	
		return $this;
	}
	
	/**
	 * TODO
	 * 获取表名，子类可覆盖获取分表表名
	 */
	public function getTable()
	{
	    return static::TABLE;
	}
}