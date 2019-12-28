<?php


namespace SqlManipulator;


use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;

/**
 * Class SqlManipulator
 * @package SqlManipulator
 * @author Erkin Eren
 */
class SqlManipulator
{
    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $parsed;

    /**
     * SqlManipulator constructor.
     * @param string $sql
     */
    public function __construct(string $sql)
    {
        $this->sql = $sql;
        $this->parsed = $this->parse($sql);
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function parse(string $sql)
    {
        return (new PHPSQLParser($sql))->parsed;
    }

    /**
     * @param $string
     * @param array $words
     * @return false|string
     */
    public function removeStartingWords($string, array $words)
    {
        foreach ($words as $word) {
            if (stripos($string, $word) === 0)
                $string = substr($string, strlen($word));
        }

        return $string;
    }

    /**
     * @param $subSql
     * @return $this
     * @throws SqlManipulatorException
     */
    public function addWhere($subSql)
    {
        $subSql = trim($subSql);
        if (!isset($this->parsed['WHERE'])) {
            $subSql = $this->removeStartingWords($subSql, ['AND ', 'OR ']);
        }
        return $this->merge("WHERE $subSql");
    }

    /**
     * @param $subSql
     * @return $this
     * @throws SqlManipulatorException
     */
    public function addJoin($subSql)
    {
        $subParsedData = $this->parse("SELECT temp FROM temp $subSql");
        unset($subParsedData['SELECT']);
        unset($subParsedData['FROM'][0]);
        return $this->merge($subParsedData);
    }

    /**
     * @param $subSql
     * @return $this
     * @throws SqlManipulatorException
     */
    public function addSelectColumn($subSql)
    {
        return $this->merge("SELECT $subSql");
    }

    /**
     * @param $subSql
     * @return $this
     * @throws SqlManipulatorException
     */
    public function addHaving($subSql)
    {
        return $this->merge("HAVING $subSql");
    }

    /**
     * @param $subSql
     * @return $this
     * @throws SqlManipulatorException
     */
    public function addGroupBy($subSql)
    {
        return $this->merge("GROUP BY $subSql");
    }

    /**
     * @param $subParsedData
     * @return $this
     * @throws SqlManipulatorException
     */
    public function merge($subParsedData)
    {
        if (is_string($subParsedData)) $subParsedData = $this->parse($subParsedData);
        if (!$subParsedData) throw new SqlManipulatorException($subParsedData);

        foreach ($subParsedData as $keyword => $subParsedDataItems) {
            if (!isset($this->parsed[$keyword])) {
                $this->parsed[$keyword] = [];
            }
            foreach ($subParsedDataItems as $index => $subParsedDataItem) {
                if ($keyword == 'SELECT') {
                    $this->parsed[$keyword][array_key_last($this->parsed[$keyword])]['delim'] = ',';
                }
                $this->parsed[$keyword][] = $subParsedDataItem;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getParsed(): array
    {
        return $this->parsed;
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->createSql($this->parsed);
    }

    /**
     * @param array $parsedData
     * @return mixed
     */
    public function createSql(array $parsedData)
    {
        return (new PHPSQLCreator($parsedData))->created;
    }
}
