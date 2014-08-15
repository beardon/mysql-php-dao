<?php

define('LOCAL_PATH', __DIR__ . '/');

require_once(LOCAL_PATH . '../../../../org/cakephp/lib/Cake/Utility/Inflector.php');

define('SOURCE_CLASSES_PATH', LOCAL_PATH . '../classes/');
define('SOURCE_CLASSES_CORE_PATH', SOURCE_CLASSES_PATH . 'dao/core/');
define('SOURCE_CLASSES_SQL_PATH', SOURCE_CLASSES_PATH . 'dao/sql/');
define('SOURCE_TEMPLATES_PATH', LOCAL_PATH . '../../../../../resources/templates/');

require_once(SOURCE_CLASSES_SQL_PATH . 'Connection.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'ConnectionFactory.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'ConnectionProperty.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'QueryExecutor.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'Transaction.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'SqlQuery.php');
require_once(SOURCE_CLASSES_PATH . 'Template.php');

define('OUTPUT_PATH', LOCAL_PATH . '../../../../../../output/');
define('CLASSES_PATH', 'classes/');
define('INTERFACES_PATH', 'interfaces/');
define('CORE_PATH', CLASSES_PATH . 'core/');
define('DAO_PATH', CLASSES_PATH . 'dao/');
define('DAO_EXT_PATH', DAO_PATH . 'ext/');
define('DTO_PATH', CLASSES_PATH . 'dto/');
define('DTO_EXT_PATH', DTO_PATH . 'ext/');
define('IDAO_PATH', INTERFACES_PATH . 'dao/');
define('SQL_PATH', CLASSES_PATH . 'sql/');

class Generator
{
    static private function convertMySQLTypeToPHPType($inType)
    {
        $outType = '';
        switch ($inType)
        {
            case 'int':
                $outType = 'int';
                break;
            case 'float':
                $outType = 'float';
                break;
            case 'tinyint':
                $outType = 'bool';
                break;
            case 'date':
            case 'datetime':
            case 'text':
            case 'varchar':
                $outType = 'string';
                break;
        }
        return $outType;
    }

    static private function createDeleteByFunction($tableName, $fieldName, $memberName, $columnType)
    {
        $parameterSetter = 'set';
        if (self::isColumnTypeNumber($columnType))
        {
            $parameterSetter .= "Number";
        }
        $code = "\t" . "/**" . "\n";
        $code .= "\t" . " * @param string \$value" . "\n";
        $code .= "\t" . " * @return int number of affected rows" . "\n";
        $code .= "\t" . " */" . "\n";
        $code .= "\t" . "public function deleteBy" . $memberName . "(\$value) {" . "\n";
        $code .= "\t\t" . "\$sql = 'DELETE FROM " . $tableName . " WHERE " . $fieldName . " = ?';" . "\n";
        $code .= "\t\t" . "\$sqlQuery = new SqlQuery(\$sql);" . "\n";
        $code .= "\t\t" . "\$sqlQuery->" . $parameterSetter . "(\$value);" . "\n";
        $code .= "\t\t" . "return \$this->executeUpdate(\$sqlQuery);" . "\n";
        $code .= "\t" . "}" . "\n\n";
        return $code;
    }

    static private function createDocBlock($shortDesc, $longDesc, $paramTags, $returnTag)
    {
        $block = "\t" . "/**" . "\n";
        $block .= "\t" . " * " . $shortDesc . "\n";
        $longDesc = trim($longDesc);
        if (!empty($longDesc))
        {
            $block .= "\t" . " * " . "\n";
            $block .= "\t" . " * " . $longDesc . "\n";
        }
        $block .= "\t" . " * " . "\n";
        for ($i = 0; $i < count($paramTags); $i++)
        {
            $block .= "\t" . " * @param " . $paramTags[$i] . "\n";
        }
        if (!empty($returnTag))
        {
            $block .= "\t" . " * " . "\n";
            $block .= "\t" . " * @return " . $returnTag . "\n";
        }
        $block .= "\t" . " */" . "\n";
        return $block;
    }

    static private function createQueryByFunction($tableName, $fieldName, $memberName, $columnType, $returnType)
    {
        $parameterSetter = 'set';
        if (self::isColumnTypeNumber($columnType))
        {
            $parameterSetter .= "Number";
        }
        $code = "\t" . "/**" . "\n";
        $code .= "\t" . " * @param string \$value" . "\n";
        $code .= "\t" . " * @return " . $returnType . "[]" . "\n";
        $code .= "\t" . " */" . "\n";
        $code .= "\t" . "public function queryBy" . $memberName . "(\$value) {" . "\n";
        $code .= "\t\t" . "\$sql = 'SELECT * FROM " . $tableName . " WHERE " . $fieldName . " = ?';" . "\n";
        $code .= "\t\t" . "\$sqlQuery = new SqlQuery(\$sql);" . "\n";
        $code .= "\t\t" . "\$sqlQuery->" . $parameterSetter . "(\$value);" . "\n";
        $code .= "\t\t" . "return \$this->getList(\$sqlQuery);" . "\n";
        $code .= "\t" . "}" . "\n\n";
        return $code;
    }

    static private function createStoredFunction($function)
    {
        $functionMySQLName = $function['Name'];
        $functionPHPName = Inflector::variable($functionMySQLName);
        $comment = $function['Comment'];
        $params = self::getRoutineParameters($functionMySQLName);
        $asFunctionParamArray = array();
        $asQueryParamArray = array();
        $paramTags = array();
        $returnTag = '';
        $j = 0;
        for ($i = 0; $i < count($params); $i++)
        {
            if (!empty($params[$i]['Mode']))
            {
                $asFunctionParamArray[$j] = '$' . $params[$i]['Name'];
                $asQueryParamArray[$j] = '?';
                $paramTags[$j] = $params[$i]['PHPType'] . ' $' . $params[$i]['Name'];
                $j++;
            } else
            {
                $returnTag = $params[$i]['PHPType'];
            }
        }
        $functionParams = implode(', ', $asFunctionParamArray);
        $queryParams = implode(', ', $asQueryParamArray);
        $routine = self::createDocBlock($functionPHPName, $comment, $paramTags, $returnTag);
        $routine .= "\t" . "static public function " . $functionPHPName . "(" . $functionParams . ")" . "\n";
        $routine .= "\t" . "{" . "\n";
        $routine .= "\t\t" . "\$sql = 'SELECT " . $functionMySQLName . "(" . $queryParams . ") AS value';" . "\n";
        $routine .= "\t\t" . "\$sqlQuery = new SqlQuery(\$sql);" . "\n";
        for ($i = 0; $i < count($params); $i++)
        {
            if (!empty($params[$i]['Mode']))
            {
                $parameterSetter = 'set';
                if (self::isColumnTypeNumber($params[$i]['PHPType']))
                {
                    $parameterSetter .= "Number";
                }
                $routine .= "\t\t" . "\$sqlQuery->" . $parameterSetter . "(\$" . $params[$i]['Name'] . ");" . "\n";
            }
        }
        $routine .= "\t\t" . "return QueryExecutor::queryForString(\$sqlQuery, 'value');" . "\n";
        $routine .= "\t" . "}" . "\n\n";
        return $routine;
    }

    static private function createStoredProcedure($procedure)
    {
        $procedureMySQLName = $procedure['Name'];
        $procedurePHPName = Inflector::variable($procedureMySQLName);
        $comment = $procedure['Comment'];
        $params = self::getRoutineParameters($procedureMySQLName);
        $asFunctionParamArray = array();
        $asQueryParamArray = array();
        $paramTags = array();
        $j = 0;
        for ($i = 0; $i < count($params); $i++)
        {
            if (!empty($params[$i]['Mode']))
            {
                $asFunctionParamArray[$j] = '$' . $params[$i]['Name'];
                $asQueryParamArray[$j] = '?';
                $paramTags[$j] = $params[$i]['PHPType'] . ' $' . $params[$i]['Name'];
                $j++;
            }
        }
        $functionParams = implode(', ', $asFunctionParamArray);
        $queryParams = implode(', ', $asQueryParamArray);
        $routine = self::createDocBlock($procedurePHPName, $comment, $paramTags, '');
        $routine .= "\t" . "static public function " . $procedurePHPName . "(" . $functionParams . ")" . "\n";
        $routine .= "\t" . "{" . "\n";
        $routine .= "\t\t" . "\$sql = 'CALL " . $procedureMySQLName . "(" . $queryParams . ")';" . "\n";
        $routine .= "\t\t" . "\$sqlQuery = new SqlQuery(\$sql);" . "\n";
        for ($i = 0; $i < count($params); $i++)
        {
            if (!empty($params[$i]['Mode']))
            {
                $parameterSetter = 'set';
                if (self::isColumnTypeNumber($params[$i]['PHPType']))
                {
                    $parameterSetter .= "Number";
                }
                $routine .= "\t\t" . "\$sqlQuery->" . $parameterSetter . "(\$" . $params[$i]['Name'] . ");" . "\n";
            }
        }
        $routine .= "\t\t" . "QueryExecutor::execute(\$sqlQuery);" . "\n";
        $routine .= "\t" . "}" . "\n\n";
        return $routine;
    }

    /**
     * @param string $tableName
     * @return bool
     */
    static private function doesTableContainPK($tableName)
    {
        $fields = self::getFields($tableName);
        for ($j = 0; $j < count($fields); $j++)
        {
            if ($fields[$j]['Key'] == 'PRI')
            {
                return true;
            }
        }
        return false;
    }

    static public function generate()
    {
        self::initialize();
        $sql = 'SHOW TABLES';
        $tables = QueryExecutor::execute(new SqlQuery($sql));
        self::generateDTOObjects($tables);
        self::generateDTOExtObjects($tables);
        self::generateDAOObjects($tables);
        self::generateDAOExtObjects($tables);
        self::generateIDAOObjects($tables);
        self::generateDAOFactory($tables);
        self::generateIncludeFile($tables);
        self::generateStoredRoutines();
    }

    /**
     * @param array $tables
     */
    static private function generateDAOExtObjects($tables)
    {
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            $tableDAOName = $tableClassBase . 'DAO';
            $tableDAOExtName = $tableDAOName . 'Ext';
            $template = new Template(SOURCE_TEMPLATES_PATH . 'DAOExt.tpl');
            $template->setPair('class_name', $tableDAOExtName);
            $template->setPair('ancestor_class_name', $tableDAOName);
            $template->setPair('table_name', $tableName);
            $template->setPair('date', date("Y-m-d H:i"));
            $file = OUTPUT_PATH . DAO_EXT_PATH . $tableDAOExtName . '.php';
            if (!file_exists($file))
            {
                $template->write($file);
            }
        }
    }

    static private function generateDAOFactory($tables)
    {
        $str = "\n";
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            $tableDAOName = $tableClassBase . 'DAO';
            $tableDAOExtName = $tableDAOName . 'Ext';
            $str .= "\t/**\n";
            $str .= "\t * @return " . $tableDAOExtName . "\n";
            $str .= "\t */\n";
            $str .= "\tpublic static function get" . $tableDAOName . "(){\n";
            $str .= "\t\treturn new " . $tableDAOExtName . "();\n";
            $str .= "\t}\n\n";
        }
        $template = new Template(SOURCE_TEMPLATES_PATH . 'DAOFactory.tpl');
        $template->setPair('content', $str);
        $template->setPair('date', date("Y-m-d H:i"));
        $template->write(OUTPUT_PATH . DAO_PATH . 'DAOFactory.php');
    }

    /**
     * @param array $tables
     */
    static private function generateDAOObjects($tables)
    {
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            $tableDAOName = $tableClassBase . 'DAO';
            $tableDAOInterfaceName = 'i' . $tableDAOName;
            $tableDTOName = $tableClassBase . 'DTO';
            $tableDTOExtName = $tableDTOName . 'Ext';
            $tableDTOVariableName = 'a' . $tableDTOName;
            $hasPK = self::doesTableContainPK($tableName);
            $fields = self::getFields($tableName);
            $parameterSetter = "\n";
            $insertFields = "";
            $updateFields = "";
            $questionMarks = "";
            $readRow = "\n";
            $pk = '';
            $pks = array();
            $queryByFunction = '';
            $deleteByFunction = '';
            $pk_type = '';
            $memberNames = array();
            $k = 1;
            for ($j = 0; $j < count($fields); $j++)
            {
                $fieldName = $fields[$j]['Field'];
                $memberName = Inflector::variable($fieldName);
                $columnType = $fields[$j]['Type'];
                if (in_array(strtolower($memberName), $memberNames))
                {
                    $k++;
                    $memberName .= $k;
                }
                $memberNames[$j] = strtolower($memberName);
                if ($fields[$j]['Key'] == 'PRI')
                {
                    $pk = $fieldName;
                    $c = count($pks);
                    $pks[$c] = $fieldName;
                    $pk_type = $columnType;
                } else
                {
                    if ($columnType != 'timestamp')
                    {
                        $insertFields .= $fieldName . ", ";
                        $updateFields .= $fieldName . " = ?, ";
                        $questionMarks .= "?, ";
                        if (self::isColumnTypeNumber($columnType))
                        {
                            $parameterSetter .= "\t\t\$sqlQuery->setNumber($" . $tableDTOVariableName . "->" . $memberName . ");\n";
                        } else
                        {
                            $parameterSetter .= "\t\t\$sqlQuery->set($" . $tableDTOVariableName . "->" . $memberName . ");\n";
                        }
                    }
                    $queryByFunction .= self::createQueryByFunction($tableName, $fieldName, ucfirst($memberName), $columnType, $tableDTOExtName);
                    $deleteByFunction .= self::createDeleteByFunction($tableName, $fieldName, ucfirst($memberName), $columnType);
                }
                $readRow .= "\t\t\$" . $tableDTOVariableName . "->" . $memberName . " = \$row['" . $fieldName . "'];\n";
            }
            if ($hasPK)
            {
                if (count($pks) == 1)
                {
                    $template = new Template(SOURCE_TEMPLATES_PATH . 'DAO.tpl');
                    if (self::isColumnTypeNumber($pk_type))
                    {
                        $template->setPair('pk_number', 'Number');
                    } else
                    {
                        $template->setPair('pk_number', '');
                    }
                } else
                {
                    $template = new Template(SOURCE_TEMPLATES_PATH . 'DAO_with_complex_pk.tpl');
                }
            } else
            {
                $template = new Template(SOURCE_TEMPLATES_PATH . 'DAOView.tpl');
            }
            $template->setPair('class_name', $tableDAOName);
            $template->setPair('dto_name', $tableDTOExtName);
            $template->setPair('interface_name', $tableDAOInterfaceName);
            $template->setPair('table_name', $tableName);
            $template->setPair('var_name', $tableDTOVariableName);

            $insertFields = substr($insertFields, 0, strlen($insertFields) - 2);
            $updateFields = substr($updateFields, 0, strlen($updateFields) - 2);
            $questionMarks = substr($questionMarks, 0, strlen($questionMarks) - 2);
            $template->setPair('pk', $pk);
            $s = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $insertFields2 = $insertFields;
            $questionMarks2 = $questionMarks;
            for ($z = 0; $z < count($pks); $z++)
            {
                $questionMarks2 .= ', ?';
                if ($z > 0)
                {
                    $s .= ', ';
                    $s2 .= ' AND ';
                    $s3 .= "\t\t";
                }
                $memberName = Inflector::variable($pks[$z]);
                $insertFields2 .= ', ' . $pks[$z];
                $s .= '$' . $memberName;
                $s2 .= $pks[$z] . ' = ? ';
                $s3 .= '$sqlQuery->setNumber($' . $memberName . ');';
                $s3 .= "\n";
                $s4 .= "\n\t\t";
                $s4 .= '$sqlQuery->setNumber($' . $tableDTOVariableName . '->' . $memberName . ');';
                $s4 .= "\n";
            }
            if ($s[0] == ',') $s = substr($s, 1);
            if ($questionMarks2[0] == ',') $questionMarks2 = substr($questionMarks2, 1);
            if ($insertFields2[0] == ',') $insertFields2 = substr($insertFields2, 1);
            $template->setPair('question_marks2', $questionMarks2);
            $template->setPair('insert_fields2', $insertFields2);
            $template->setPair('pk_set_update', $s4);
            $template->setPair('pk_set', $s3);
            $template->setPair('pk_where', $s2);
            $template->setPair('pks', $s);
            $template->setPair('pk_php', Inflector::variable($pk));
            $template->setPair('insert_fields', $insertFields);
            $template->setPair('read_row', $readRow);
            $template->setPair('update_fields', $updateFields);
            $template->setPair('question_marks', $questionMarks);
            $template->setPair('parameter_setter', $parameterSetter);
            $template->setPair('read_row', $readRow);
            $template->setPair('date', date("Y-m-d H:i"));
            $template->setPair('queryByFieldFunctions', $queryByFunction);
            $template->setPair('deleteByFieldFunctions', $deleteByFunction);
            $template->write(OUTPUT_PATH . DAO_PATH . $tableDAOName . '.php');
        }
    }

    /**
     * @param array $tables
     */
    static private function generateDTOExtObjects($tables)
    {
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            if ($tableClassBase[strlen($tableClassBase) - 1] == 's')
            {
                $tableClassBase = substr($tableClassBase, 0, strlen($tableClassBase) - 1);
            }
            $tableDTOName = $tableClassBase . 'DTO';
            $tableDTOExtName = $tableDTOName . 'Ext';
            $template = new Template(SOURCE_TEMPLATES_PATH . 'DTOExt.tpl');
            $template->setPair('class_name', $tableDTOExtName);
            $template->setPair('ancestor_class_name', $tableDTOName);
            $template->setPair('table_name', $tableName);
            $template->setPair('date', date("Y-m-d H:i"));
            $file = OUTPUT_PATH . DTO_EXT_PATH . $tableDTOExtName . '.php';
            if (!file_exists($file))
            {
                $template->write($file);
            }
        }
    }

    /**
     * @param array $tables
     */
    static private function generateDTOObjects($tables)
    {
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            if ($tableClassBase[strlen($tableClassBase) - 1] == 's')
            {
                $tableClassBase = substr($tableClassBase, 0, strlen($tableClassBase) - 1);
            }
            $tableDTOName = $tableClassBase . 'DTO';
            $template = new Template(SOURCE_TEMPLATES_PATH . 'DTO.tpl');
            $template->setPair('class_name', $tableDTOName);
            $template->setPair('table_name', $tableName);
            $fields = self::getFields($tableName);
            $members = "\r\n";
            for ($j = 0; $j < count($fields); $j++)
            {
                $members .= "\t\tvar $" . Inflector::variable($fields[$j]['Field']) . ";\n\r";
            }
            $template->setPair('variables', $members);
            $template->setPair('date', date("Y-m-d H:i"));
            $template->write(OUTPUT_PATH . DTO_PATH . $tableDTOName . '.php');
        }
    }

    static private function generateIDAOObjects($tables)
    {
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            $tableDAOName = $tableClassBase . 'DAO';
            $tableIDAOName = 'i' . $tableDAOName;
            $tableDTOName = $tableClassBase . 'DTO';
            $tableDTOExtName = $tableDTOName . 'Ext';
            $tableDTOVariableName = 'a' . $tableDTOExtName;
            $hasPK = self::doesTableContainPK($tableName);
            $fields = self::getFields($tableName);
            $parameterSetter = "\n";
            $insertFields = "";
            $updateFields = "";
            $questionMarks = "";
            $readRow = "\n";
            $pk = '';
            $pks = array();
            $queryByField = '';
            $deleteByField = '';
            $memberNames = array();
            $k = 1;
            for ($j = 0; $j < count($fields); $j++)
            {
                $fieldName = $fields[$j]['Field'];
                $memberName = Inflector::variable($fieldName);
                $columnType = $fields[$j]['Type'];
                if (in_array(strtolower($memberName), $memberNames))
                {
                    $k++;
                    $memberName .= $k;
                }
                $memberNames[$j] = strtolower($memberName);
                if ($fields[$j]['Key'] == 'PRI')
                {
                    $pk = $fieldName;
                    $c = count($pks);
                    $pks[$c] = $fieldName;
                } else
                {
                    $insertFields .= $fieldName . ", ";
                    $updateFields .= $fieldName . " = ?, ";
                    $questionMarks .= "?, ";
                    if (self::isColumnTypeNumber($columnType))
                    {
                        $parameterSetter .= "\t\t" . "\$sqlQuery->setNumber($" . $tableDTOVariableName . "->" . $memberName . ");\n";
                    } else
                    {
                        $parameterSetter .= "\t\t" . "\$sqlQuery->set($" . $memberName . ');' . "\n";
                    }
                    $queryByField .= "\tpublic function queryBy" . ucfirst($memberName) . "(\$value);\n\n";
                    $deleteByField .= "\tpublic function deleteBy" . ucfirst($memberName) . "(\$value);\n\n";
                }
                $readRow .= "\t\t\$" . $tableDTOVariableName . "->" . $memberName . " = \$row['" . $fieldName . "'];\n";
            }

            if ($hasPK)
            {
                if (count($pks) == 1)
                {
                    $template = new Template(SOURCE_TEMPLATES_PATH . 'IDAO.tpl');
                } else
                {
                    $template = new Template(SOURCE_TEMPLATES_PATH . 'IDAO_with_complex_pk.tpl');
                }
            } else
            {
                $template = new Template(SOURCE_TEMPLATES_PATH . 'IDAOView.tpl');
            }

            $template->setPair('class_name', $tableIDAOName);
            $template->setPair('table_name', $tableName);
            $template->setPair('type_name', $tableDTOExtName);
            $template->setPair('var_name', $tableDTOVariableName);

            $s = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $insertFields2 = $insertFields;
            $questionMarks2 = $questionMarks;
            for ($z = 0; $z < count($pks); $z++)
            {
                $questionMarks2 .= ', ?';
                if ($z > 0)
                {
                    $s .= ', ';
                    $s2 .= ' AND ';
                    $s3 .= "\t\t";
                }
                $memberName = Inflector::variable($pks[$z]);
                $insertFields2 .= ', ' . $memberName;
                $s .= '$' . $memberName;
                $s2 .= $memberName . ' = ? ';
                $s3 .= '$sqlQuery->setNumber(' . $memberName . ');';
                $s3 .= "\n";
                $s4 .= "\n\t\t";
                $s4 .= '$sqlQuery->setNumber($' . $tableDTOVariableName . '->' . $memberName . ');';
                $s4 .= "\n";
            }
            $template->setPair('question_marks2', $questionMarks2);
            $template->setPair('insert_fields2', $insertFields2);
            $template->setPair('pk_set_update', $s4);
            $template->setPair('pk_set', $s3);
            $template->setPair('pk_where', $s2);
            $template->setPair('pks', $s);

            $insertFields = substr($insertFields, 0, strlen($insertFields) - 2);
            $updateFields = substr($updateFields, 0, strlen($updateFields) - 2);
            $questionMarks = substr($questionMarks, 0, strlen($questionMarks) - 2);
            $template->setPair('pk', $pk);
            $template->setPair('insert_fields', $insertFields);
            $template->setPair('read_row', $readRow);
            $template->setPair('update_fields', $updateFields);
            $template->setPair('question_marks', $questionMarks);
            $template->setPair('parameter_setter', $parameterSetter);
            $template->setPair('read_row', $readRow);
            $template->setPair('date', date("Y-m-d H:i"));
            $template->setPair('queryByFieldFunctions', $queryByField);
            $template->setPair('deleteByFieldFunctions', $deleteByField);
            $template->write(OUTPUT_PATH . IDAO_PATH . $tableIDAOName . '.php');
        }
    }

    /**
     * @param array $tables
     */
    static private function generateIncludeFile($tables)
    {
        $str = "\n";
        for ($i = 0; $i < count($tables); $i++)
        {
            $tableName = $tables[$i][0];
            $tableClassBase = Inflector::classify($tableName);
            $tableDAOName = $tableClassBase . 'DAO';
            $tableDAOExtName = $tableDAOName . 'Ext';
            $tableIDAOName = 'i' . $tableDAOName;
            $tableDTOName = $tableClassBase . 'DTO';
            $tableDTOExtName = $tableDTOName . 'Ext';
            $str .= "\trequire_once('" . IDAO_PATH . $tableIDAOName . ".php');\n";
            $str .= "\trequire_once('" . DAO_PATH . $tableDAOName . ".php');\n";
            $str .= "\trequire_once('" . DAO_EXT_PATH . $tableDAOExtName . ".php');\n";
            $str .= "\trequire_once('" . DTO_PATH . $tableDTOName . ".php');\n";
            $str .= "\trequire_once('" . DTO_EXT_PATH . $tableDTOExtName . ".php');\n";
        }
        $template = new Template(SOURCE_TEMPLATES_PATH . 'DAOIncludes.tpl');
        $template->setPair('include', $str);
        $template->write(OUTPUT_PATH . 'DAOIncludes.php');
    }

    static private function generateStoredRoutines()
    {
        $str = '';
        $sql = 'SHOW FUNCTION STATUS WHERE Db = "' . ConnectionProperty::getDatabase() . '"';
        $functions = QueryExecutor::execute(new SqlQuery($sql));
        for ($i = 0; $i < count($functions); $i++)
        {
            $str .= self::createStoredFunction($functions[$i]);
        }
        $sql = 'SHOW PROCEDURE STATUS WHERE Db = "' . ConnectionProperty::getDatabase() . '"';
        $procedures = QueryExecutor::execute(new SqlQuery($sql));
        for ($i = 0; $i < count($procedures); $i++)
        {
            $str .= self::createStoredProcedure($procedures[$i]);
        }
        $template = new Template(SOURCE_TEMPLATES_PATH . 'StoredRoutines.tpl');
        $template->setPair('date', date("Y-m-d H:i"));
        $template->setPair('functions', $str);
        $template->write(OUTPUT_PATH . CLASSES_PATH . 'StoredRoutines.php');
    }

    /**
     * @param string $table
     * @return array
     */
    static private function getFields($table)
    {
        $sql = 'DESC ' . $table;
        return QueryExecutor::execute(new SqlQuery($sql));
    }

    static private function getRoutineParameters($routineName)
    {
        $sql = 'SELECT * ';
        $sql .= 'FROM information_schema.parameters ';
        $sql .= 'WHERE SPECIFIC_SCHEMA = "' . ConnectionProperty::getDatabase() . '" AND SPECIFIC_NAME = "' . $routineName . '"';
        $params = QueryExecutor::execute(new SqlQuery($sql));
        $paramArray = array();
        for ($i = 0; $i < count($params); $i++)
        {
            $paramName = Inflector::variable($params[$i]['PARAMETER_NAME']);
            $paramMode = $params[$i]['PARAMETER_MODE'];
            $paramMySQLType = $params[$i]['DATA_TYPE'];
            $paramPHPType = self::convertMySQLTypeToPHPType($paramMySQLType);
            $paramArray[$i]['Name'] = $paramName;
            $paramArray[$i]['Mode'] = $paramMode;
            $paramArray[$i]['PHPType'] = $paramPHPType;
            $paramArray[$i]['MySQLType'] = $paramMySQLType;
        }
        return $paramArray;
    }

    static private function initialize()
    {
        @mkdir(OUTPUT_PATH);
        @mkdir(OUTPUT_PATH . CLASSES_PATH);
        @mkdir(OUTPUT_PATH . CORE_PATH);
        @mkdir(OUTPUT_PATH . DAO_PATH);
        @mkdir(OUTPUT_PATH . DAO_EXT_PATH);
        @mkdir(OUTPUT_PATH . DTO_PATH);
        @mkdir(OUTPUT_PATH . DTO_EXT_PATH);
        @mkdir(OUTPUT_PATH . SQL_PATH);
        @mkdir(OUTPUT_PATH . INTERFACES_PATH);
        @mkdir(OUTPUT_PATH . IDAO_PATH);
        copy(SOURCE_CLASSES_CORE_PATH . 'ArrayList.php', OUTPUT_PATH . CORE_PATH . 'ArrayList.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'Connection.php', OUTPUT_PATH . SQL_PATH . 'Connection.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'ConnectionFactory.php', OUTPUT_PATH . SQL_PATH . 'ConnectionFactory.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'ConnectionProperty.php', OUTPUT_PATH . SQL_PATH . 'ConnectionProperty.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'QueryExecutor.php', OUTPUT_PATH . SQL_PATH . 'QueryExecutor.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'Transaction.php', OUTPUT_PATH . SQL_PATH . 'Transaction.php');
        copy(SOURCE_CLASSES_SQL_PATH . 'SqlQuery.php', OUTPUT_PATH . SQL_PATH . 'SqlQuery.php');
    }

    static private function isColumnTypeNumber($columnType)
    {
        if (strtolower(substr($columnType, 0, 3)) == 'int' || strtolower(substr($columnType, 0, 7)) == 'tinyint')
        {
            return true;
        }
        return false;
    }

}