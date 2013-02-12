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
	 * Get DTO object by primry key
	 *
	 * @param string ${pks} primary key
	 * @return ${type_name} 
	 */
	public function load(${pks});

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
	
	/**
 	 * Delete record from table
	 *
 	 * @param int ${pks} primary key
 	 */
	public function delete(${pks});
	
	/**
 	 * Insert record to table
 	 *
 	 * @param ${type_name} $${var_name}
 	 */
	public function insert($${var_name});
	
	/**
 	 * Update record in table
 	 *
 	 * @param ${type_name} $${var_name}
 	 */
	public function update($${var_name});	

	/**
	 * Delete all rows
	 */
	public function clean();

${queryByFieldFunctions}
${deleteByFieldFunctions}
}
?>