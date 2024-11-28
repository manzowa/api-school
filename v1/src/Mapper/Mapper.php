<?php 

/**
 *  Mapper
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category Assessment
 * @package  SchoolManager
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\SchoolManager\Mapper;

use \Exception;
use Iterator;
use \PDOStatement;
use \TypeError;

abstract class Mapper 
{
    protected PDOStatement $stmt;
    protected string $command;
    protected string $query;
    protected mixed $results;
    protected ?int $stockId;

    public function __construct(protected ?\PDO $db) {}
    /**
     * Method rowCount
     * 
     * @return int;
     */
    public function rowCount(): int  
    {
        if (!is_null($this->stmt)) {
            return $this->stmt->rowCount();
        }
        return 0;
    }
     /**
     * Method lastInsertId
     * 
     * @return int;
     */
    public function lastInsertId(): int  
    {
        if ($this->connexion() instanceof \PDO) {
            return $this->connexion()->lastInsertId();
        }
        return 0;
    }
    /**
	 * Method getResults
	 *
	 * @return mixed
	 */
	public function getResults() {
		return $this->results?? false;
	}
    public function bindParam(
        string|int $param, mixed $var, int $type = \PDO::PARAM_STR,
        int $maxLength = 0,mixed $driverOptions = null
    ): self {
        $this->stmt->bindParam($param, $var, $type, $maxLength, $driverOptions);
        return $this;
    }
    public function bindValue(string|int $param, mixed $value, int $type = \PDO::PARAM_STR):self {
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }
    public function query(string $queryString):self {
        $this->stmt = $this->connexion()->query($queryString);
        return $this;
    }
    public function prepare(string $queryString) {
        $this->stmt = $this->connexion()->prepare($queryString);
        return $this;
    }
    public function fetch(
        int $mode = \PDO::FETCH_DEFAULT, int $cursorOrientation = \PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0
    ) {
        $this->results = $this->stmt->fetch($mode, $cursorOrientation, $cursorOffset);
        return $this;
    }
    public function fetchAll(
        int $mode, mixed $typeMode = null, 
        ?array $constructorArgs = null, $class = null
    ): self {
        if (in_array($mode, [
                \PDO::FETCH_DEFAULT, \PDO::FETCH_BOTH,
                \PDO::FETCH_COLUMN, \PDO::FETCH_GROUP, 
                \PDO::FETCH_ASSOC
            ]) && is_null($typeMode) && is_null($constructorArgs)
         ) {
            $this->results = $this->stmt->fetchAll($mode); 
        } elseif($mode === \PDO::FETCH_COLUMN && is_numeric($typeMode)) {
            $this->results = $this->stmt->fetchAll($mode, $typeMode);
        } elseif($mode === \PDO::FETCH_FUNC && is_callable($typeMode)) {
            $this->results = $this->stmt->fetchAll($mode, $typeMode);
        } elseif ($mode === \PDO::FETCH_CLASS && !is_null($typeMode) &&  !is_null($constructorArgs)) {
            $this->results = $this->stmt->fetchAll($mode, $typeMode, $constructorArgs);
        } else {
            $this->results = $this->stmt->fetchAll(); 
        }

      
        return $this;
    }
    public function closeCursor(): self {
        $this->stmt->closeCursor();
        return $this;
    }
    /**
     * Get the value of stockId
     *
     * @return ?int
     */
    public function getStockId(): ?int {
        return $this->stockId;
    }
    /**
     * Set the value of stockId
     *
     * @param ?int $stockId
     *
     * @return self
     */
    public function setStockId(?int $stockId): self {
        $this->stockId = $stockId;
        return $this;
    }
    /**
     * Get 
     */
    public function connexion(){
        return $this->db; 
    }
    public function beginTransaction() {
        $this->connexion()->beginTransaction();
        return $this;
    }
    public function inTransaction() {
        return $this->connexion()->inTransaction();
    }
    public function rollBack() {
        return $this->connexion()->rollBack();
    }
    public function commit() 
    {
        return $this->connexion()->commit();
    }

    /**
     * Method create
     * 
     * @param ?string $table - 
     * @param array $rows  - 
     * 
     * @return self
     */
    public function create(?string $table, array $rows = []): self
    {
        if (!is_null($table) && count($rows)>0) {
            $command = 'INSERT INTO '.$table;
            $row = null;
            $value = null;
            foreach (array_keys($rows) as $key) {
                $row .=",".$key;
                $value .=", :".$key;
            }
            $command .="(".substr($row, 1).") ";
            $command .="VALUES (".substr($value, 1).")";
            $this->prepare($command);
        }
        return $this;
    }
    /**
     * Method select
     * 
     * @param ?string $table - 
     * @param array $rows  - 
     * 
     * @return self
     */
    public function select(string $table, array $rows = []): self 
    {
        if (!is_null($table) && count($rows)>0) {
            $command = 'SELECT '.trim(join($rows), ',').' FROM '.$table;
            $this->prepare($command);
        }
        return $this;
    }

    /**
     * Set the value of results
     *
     * @param mixed $results
     *
     * @return self
     */
    public function setResults(mixed $results): self {
        $this->results = $results;
        return $this;
    }
    public function execute(?array $params = null): self {
        $this->stmt->execute($params);
        return $this;
    }
    public function executeUpdate(?array $params = null): self {
        try {
            $this->execute($params)
                ->setResults(true);
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->setResults(false);
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function executeQuery(?array $params = null): self
    {
        try {
            $this->execute($params);
            $rows = [];
            while($row = $this->fetch(\PDO::FETCH_ASSOC)->getResults()) {
                $rows[] = $row;
            }
            $this->setResults($rows);
            $this->closeCursor();
        } catch (\PDOException $e) {
            $this->setResults(false);
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function executInsert(?array $params = null): self {
        try {
            $this->execute($params)
                ->setResults(true);
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->setResults(false);
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function executeDelete(?array $params = null): self {
        try {
            $this->execute($params)
                ->setResults(true);
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->setResults(false);
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function exec(string $queryString): self {
        try {
            $count = $this->connexion()->exec($queryString);
            $this->setResults($count);
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            \App\SchoolManager\loggerException($e);
            $this->setResults(false);
        }
        return $this;
    }
}   