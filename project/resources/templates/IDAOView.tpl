<?php
/**
 * Interface DAO
 *
 * @version 2.7
 * @date ${date}
 *
 * Original author
 * @author http://phpdao.com
 * 
 * Revision 2.7
 * @author Aaron Bean <aaron.bean@beardon.com>
 */
interface ${class_name} {

	/**
	 * Get all records from table
	 */
	public function queryAll();
	
	/**
	 * Get all records from table ordered by field
	 *
	 * @param string $orderColumn column name
	 */
	public function queryAllOrderBy($orderColumn);
	
${queryByFieldFunctions}
}