<?php
/**
 * Object represents sql query with params
 *
 * @version 2.7
 * @date 2013-02-12
 *
 * Original author
 * @author http://phpdao.com
 * 
 * Revision 2.7
 * @author Aaron Bean <aaron.bean@beardon.com>
 */
class SqlQuery{
	var $txt;
	var $params = array();
	var $idx = 0;

	/**
	 * Constructor
	 *
	 * @param String $txt zapytanie sql
	 */
	function SqlQuery($txt){
		$this->txt = $txt;
	}

	/**
	 * Set string param
	 *
	 * @param String $value value set
	 */
	public function setString($value){
		$value = mysql_escape_string($value);
		$this->params[$this->idx++] = "'".$value."'";
	}
	
	/**
	 * Set string param
	 *
	 * @param String $value value to set
	 */
	public function set($value){
		$value = mysql_escape_string($value);
		$this->params[$this->idx++] = "'".$value."'";
	}
	

	/**
	 * Metoda zamienia znaki zapytania
	 * na wartosci przekazane jako parametr metody
	 *
	 * @param String $value wartosc do wstawienia
	 */
	public function setNumber($value){
		if($value===null){
			$this->params[$this->idx++] = "null";
			return;
		}
		if(!is_numeric($value)){
			throw new Exception($value.' is not a number');
		}
		$this->params[$this->idx++] = "'".$value."'";
	}

	/**
	 * Get sql query
	 *
	 * @return String
	 */
	public function getQuery(){
		if($this->idx==0){
			return $this->txt;
		}
		$p = explode("?", $this->txt);
		$sql = '';
		for($i=0;$i<=$this->idx;$i++){
			if($i>=count($this->params)){
				$sql .= $p[$i];
			}else{
				$sql .= $p[$i].$this->params[$i];
			}
		}
		return $sql;
	}
	
	/**
	 * Function replace first char
	 *
	 * @param String $str
	 * @param String $old
	 * @param String $new
	 * @return String
	 */
	private function replaceFirst($str, $old, $new){
		$len = strlen($str);
		for ($i=0;$i<$len;$i++){
			if($str[$i]==$old){
				$str = substr($str,0,$i).$new.substr($str,$i+1);
				return $str;
			}
		}
		return $str;
	}
}
?>