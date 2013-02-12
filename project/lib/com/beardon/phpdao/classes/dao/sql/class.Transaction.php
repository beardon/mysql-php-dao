<?php
/**
 * Database transaction
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
class Transaction{
	private static $transactions;

	private $connection;

	public function Transaction(){
		$this->connection = new Connection();
		if(!Transaction::$transactions){
			Transaction::$transactions = new ArrayList();
		}
		Transaction::$transactions->add($this);
		$this->connection->executeQuery('BEGIN');
	}

	/**
	 * Zakonczenie transakcji i zapisanie zmian
	 */
	public function commit(){
		$this->connection->executeQuery('COMMIT');
		$this->connection->close();
		Transaction::$transactions->removeLast();
	}

	/**
	 * Zakonczenie transakcji i wycofanie zmian
	 */
	public function rollback(){
		$this->connection->executeQuery('ROLLBACK');
		$this->connection->close();
		Transaction::$transactions->removeLast();
	}

	/**
	 * Pobranie polaczenia dla obencej transakcji
	 *
	 * @return polazenie do bazy
	 */
	public function getConnection(){
		return $this->connection;
	}

	/**
	 * Zwraca obecna transakcje
	 *
	 * @return transkacja
	 */
	public static function getCurrentTransaction(){
		if(Transaction::$transactions){
			$tran = Transaction::$transactions->getLast();
			return $tran;
		}
		return;
	}
}
?>