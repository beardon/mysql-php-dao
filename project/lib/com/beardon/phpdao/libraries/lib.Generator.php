<?php

define('LOCAL_PATH', __DIR__ . '/');
define('SOURCE_CLASSES_PATH', LOCAL_PATH . '../classes/');
define('SOURCE_CLASSES_CORE_PATH', SOURCE_CLASSES_PATH . 'dao/core/');
define('SOURCE_CLASSES_SQL_PATH', SOURCE_CLASSES_PATH . 'dao/sql/');
define('SOURCE_TEMPLATES_PATH', LOCAL_PATH . '../../../../../resources/templates/');

require_once(SOURCE_CLASSES_SQL_PATH . 'class.Connection.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'class.ConnectionFactory.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'class.ConnectionProperty.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'class.QueryExecutor.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'class.Transaction.php');
require_once(SOURCE_CLASSES_SQL_PATH . 'class.SqlQuery.php');
require_once(SOURCE_CLASSES_PATH . 'class.Template.php');

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

function generate()
{
    init();
    $sql = 'SHOW TABLES';
    $tablesArray = QueryExecutor::execute(new SqlQuery($sql));
    generateDTOObjects($tablesArray);
    generateDTOExtObjects($tablesArray);
    generateDAOObjects($tablesArray);
    generateDAOExtObjects($tablesArray);
    generateIDAOObjects($tablesArray);
    createIncludeFile($tablesArray);
    createDAOFactory($tablesArray);
}

function init()
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
    copy(SOURCE_CLASSES_CORE_PATH . 'class.ArrayList.php', OUTPUT_PATH . CORE_PATH . 'class.ArrayList.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.Connection.php', OUTPUT_PATH . SQL_PATH . 'class.Connection.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.ConnectionFactory.php', OUTPUT_PATH . SQL_PATH . 'class.ConnectionFactory.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.ConnectionProperty.php', OUTPUT_PATH . SQL_PATH . 'class.ConnectionProperty.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.QueryExecutor.php', OUTPUT_PATH . SQL_PATH . 'class.QueryExecutor.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.Transaction.php', OUTPUT_PATH . SQL_PATH . 'class.Transaction.php');
    copy(SOURCE_CLASSES_SQL_PATH . 'class.SqlQuery.php', OUTPUT_PATH . SQL_PATH . 'class.SqlQuery.php');
}

/**
 * @param array $tables
 */
function createIncludeFile($tables)
{
    $str = "\n";
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
        $tableDAOName = $tableClassBase . 'DAO';
        $tableDAOExtName = $tableDAOName . 'Ext';
        $tableIDAOName = 'i' . $tableDAOName;
        $tableDTOName = $tableClassBase . 'DTO';
        $tableDTOExtName = $tableDTOName . 'Ext';
        $str .= "\trequire_once('" . IDAO_PATH . "interface." . $tableIDAOName . ".php');\n";
        $str .= "\trequire_once('" . DAO_PATH . "class." . $tableDAOName . ".php');\n";
        $str .= "\trequire_once('" . DAO_EXT_PATH . "class." . $tableDAOExtName . ".php');\n";
        $str .= "\trequire_once('" . DTO_PATH . "class." . $tableDTOName . ".php');\n";
        $str .= "\trequire_once('" . DTO_EXT_PATH . "class." . $tableDTOExtName . ".php');\n";
    }
    $template = new Template(SOURCE_TEMPLATES_PATH . 'include_dao.tpl');
    $template->setPair('include', $str);
    $template->write(OUTPUT_PATH . 'include_dao.php');
}

/**
 * @param string $tableName
 * @return bool
 */
function doesTableContainPK($tableName)
{
    $fieldArray = getFields($tableName);
    for ($j = 0; $j < count($fieldArray); $j++)
    {
        if ($fieldArray[$j][3] == 'PRI')
        {
            return true;
        }
    }
    return false;
}

function createDAOFactory($tables)
{
    $str = "\n";
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
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
    $template->write(OUTPUT_PATH . DAO_PATH . 'class.DAOFactory.php');
}

/**
 * @param array $tables
 */
function generateDTOExtObjects($tables)
{
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
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
        $file = OUTPUT_PATH . DTO_EXT_PATH . 'class.' . $tableDTOExtName . '.php';
        if (!file_exists($file))
        {
            $template->write($file);
        }
    }
}

/**
 * @param array $tables
 */
function generateDTOObjects($tables)
{
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
        if ($tableClassBase[strlen($tableClassBase) - 1] == 's')
        {
            $tableClassBase = substr($tableClassBase, 0, strlen($tableClassBase) - 1);
        }
        $tableDTOName = $tableClassBase . 'DTO';
        $template = new Template(SOURCE_TEMPLATES_PATH . 'DTO.tpl');
        $template->setPair('class_name', $tableDTOName);
        $template->setPair('table_name', $tableName);
        $fieldArray = getFields($tableName);
        $fields = "\r\n";
        for ($j = 0; $j < count($fieldArray); $j++)
        {
            $fields .= "\t\tvar $" . getVarNameWithS($fieldArray[$j][0]) . ";\n\r";
        }
        $template->setPair('variables', $fields);
        $template->setPair('date', date("Y-m-d H:i"));
        $template->write(OUTPUT_PATH . DTO_PATH . 'class.' . $tableDTOName . '.php');
    }
}

/**
 * @param array $tables
 */
function generateDAOExtObjects($tables)
{
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
        $tableDAOName = $tableClassBase . 'DAO';
        $tableDAOExtName = $tableDAOName . 'Ext';
        $template = new Template(SOURCE_TEMPLATES_PATH . 'DAOExt.tpl');
        $template->setPair('class_name', $tableDAOExtName);
        $template->setPair('ancestor_class_name', $tableDAOName);
        $template->setPair('table_name', $tableName);
        $template->setPair('var_name', getVarName($tableName));
        $template->setPair('date', date("Y-m-d H:i"));
        $file = OUTPUT_PATH . DAO_EXT_PATH . 'class.' . $tableDAOExtName . '.php';
        if (!file_exists($file))
        {
            $template->write($file);
        }
    }
}

/**
 * @param array $tables
 */
function generateDAOObjects($tables)
{
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
        $tableDAOName = $tableClassBase . 'DAO';
        $tableDAOInterfaceName = 'i' . $tableDAOName;
        $tableDTOName = $tableClassBase . 'DTO';
        $tableDTOExtName = $tableDTOName . 'Ext';
        $tableDTOVariableName = 'a' . $tableDTOName;
        $hasPK = doesTableContainPK($tableName);
        $fieldArray = getFields($tableName);
        $parameterSetter = "\n";
        $insertFields = "";
        $updateFields = "";
        $questionMarks = "";
        $readRow = "\n";
        $pk = '';
        $pks = array();
        $queryByField = '';
        $deleteByField = '';
        $pk_type = '';
        for ($j = 0; $j < count($fieldArray); $j++)
        {
            if ($fieldArray[$j][3] == 'PRI')
            {
                $pk = $fieldArray[$j][0];
                $c = count($pks);
                $pks[$c] = $fieldArray[$j][0];
                $pk_type = $fieldArray[$j][1];
            } else
            {
                $insertFields .= $fieldArray[$j][0] . ", ";
                $updateFields .= $fieldArray[$j][0] . " = ?, ";
                $questionMarks .= "?, ";
                if (isColumnTypeNumber($fieldArray[$j][1]))
                {
                    $parameterSetter .= "\t\t\$sqlQuery->setNumber($" . $tableDTOVariableName . "->" . getVarNameWithS($fieldArray[$j][0]) . ");\n";
                } else
                {
                    $parameterSetter .= "\t\t\$sqlQuery->set($" . $tableDTOVariableName . "->" . getVarNameWithS($fieldArray[$j][0]) . ");\n";
                }
                $parameterSetter2 = '';
                if (isColumnTypeNumber($fieldArray[$j][1]))
                {
                    $parameterSetter2 .= "Number";
                }
                $queryByField .= "	public function queryBy" . getClassName($fieldArray[$j][0]) . "(\$value){
		\$sql = 'SELECT * FROM " . $tableName . " WHERE " . $fieldArray[$j][0] . " = ?';
		\$sqlQuery = new SqlQuery(\$sql);
		\$sqlQuery->set" . $parameterSetter2 . "(\$value);
		return \$this->getList(\$sqlQuery);
	}\n\n";
                $deleteByField .= "	public function deleteBy" . getClassName($fieldArray[$j][0]) . "(\$value){
		\$sql = 'DELETE FROM " . $tableName . " WHERE " . $fieldArray[$j][0] . " = ?';
		\$sqlQuery = new SqlQuery(\$sql);
		\$sqlQuery->set" . $parameterSetter2 . "(\$value);
		return \$this->executeUpdate(\$sqlQuery);
	}\n\n";
            }
            $readRow .= "\t\t\$" . $tableDTOVariableName . "->" . getVarNameWithS($fieldArray[$j][0]) . " = \$row['" . $fieldArray[$j][0] . "'];\n";
        }
        if ($hasPK)
        {
            if (count($pks) == 1)
            {
                $template = new Template(SOURCE_TEMPLATES_PATH . 'DAO.tpl');
                echo '$pk_type ' . $pk_type . '<br/>';
                if (isColumnTypeNumber($pk_type))
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
        }
        else
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
            $insertFields2 .= ', ' . $pks[$z];
            $s .= '$' . getVarNameWithS($pks[$z]);
            $s2 .= $pks[$z] . ' = ? ';
            $s3 .= '$sqlQuery->setNumber($' . getVarNameWithS($pks[$z]) . ');';
            $s3 .= "\n";
            $s4 .= "\n\t\t";
            $s4 .= '$sqlQuery->setNumber($' . getVarName($tableName) . '->' . getVarNameWithS($pks[$z]) . ');';
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
        $template->setPair('pk_php', getVarNameWithS($pk));
        $template->setPair('insert_fields', $insertFields);
        $template->setPair('read_row', $readRow);
        $template->setPair('update_fields', $updateFields);
        $template->setPair('question_marks', $questionMarks);
        $template->setPair('parameter_setter', $parameterSetter);
        $template->setPair('read_row', $readRow);
        $template->setPair('date', date("Y-m-d H:i"));
        $template->setPair('queryByFieldFunctions', $queryByField);
        $template->setPair('deleteByFieldFunctions', $deleteByField);
        $template->write(OUTPUT_PATH . DAO_PATH . 'class.' . $tableDAOName . '.php');
    }
}

function isColumnTypeNumber($columnType)
{
    echo $columnType . '<br/>';
    if (strtolower(substr($columnType, 0, 3)) == 'int' || strtolower(substr($columnType, 0, 7)) == 'tinyint')
    {
        return true;
    }
    return false;
}

function generateIDAOObjects($tables)
{
    for ($i = 0; $i < count($tables); $i++)
    {
        $tableName = $tables[$i][0];
        $tableClassBase = getClassName($tableName);
        $tableDAOName = $tableClassBase . 'DAO';
        $tableIDAOName = 'i' . $tableDAOName;
        $tableDTOName = $tableClassBase . 'DTO';
        $tableDTOExtName = $tableDTOName . 'Ext';
        $tableDTOVariableName = 'a' . $tableDTOExtName;
        $hasPK = doesTableContainPK($tableName);
        $fieldArray = getFields($tableName);
        $parameterSetter = "\n";
        $insertFields = "";
        $updateFields = "";
        $questionMarks = "";
        $readRow = "\n";
        $pk = '';
        $pks = array();
        $queryByField = '';
        $deleteByField = '';
        for ($j = 0; $j < count($fieldArray); $j++)
        {
            if ($fieldArray[$j][3] == 'PRI')
            {
                $pk = $fieldArray[$j][0];
                $c = count($pks);
                $pks[$c] = $fieldArray[$j][0];
            } else
            {
                $insertFields .= $fieldArray[$j][0] . ", ";
                $updateFields .= $fieldArray[$j][0] . " = ?, ";
                $questionMarks .= "?, ";
                if (isColumnTypeNumber($fieldArray[$j][1]))
                {
                    $parameterSetter .= "\t\t\$sqlQuery->setNumber($" . getVarName($tableName) . "->" . getVarNameWithS($fieldArray[$j][0]) . ");\n";
                } else
                {
                    $parameterSetter .= "\t\t" . '$sqlQuery->set($' . getVarName($fieldArray[$j][0]) . ');' . "\n";
                }
                $queryByField .= "\tpublic function queryBy" . getClassName($fieldArray[$j][0]) . "(\$value);\n\n";
                $deleteByField .= "\tpublic function deleteBy" . getClassName($fieldArray[$j][0]) . "(\$value);\n\n";
            }
            $readRow .= "\t\t\$" . getVarName($tableName) . "->" . getVarNameWithS($fieldArray[$j][0]) . " = \$row['" . $fieldArray[$j][0] . "'];\n";
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
        }
        else
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
            $insertFields2 .= ', ' . getVarNameWithS($pks[$z]);
            $s .= '$' . getVarNameWithS($pks[$z]);
            $s2 .= getVarNameWithS($pks[$z]) . ' = ? ';
            $s3 .= '$sqlQuery->setNumber(' . getVarName($pks[$z]) . ');';
            $s3 .= "\n";
            $s4 .= "\n\t\t";
            $s4 .= '$sqlQuery->setNumber($' . getVarName($tableName) . '->' . getVarNameWithS($pks[$z]) . ');';
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
        $template->write(OUTPUT_PATH . IDAO_PATH . 'interface.' . $tableIDAOName . '.php');
    }
}

/**
 * @param string $table
 * @return array
 */
function getFields($table)
{
    $sql = 'DESC ' . $table;
    error_log($sql);
    return QueryExecutor::execute(new SqlQuery($sql));
}


function getClassName($tableName)
{
    $tableName = strtoupper($tableName[0]) . substr($tableName, 1);
    for ($i = 0; $i < strlen($tableName); $i++)
    {
        if ($tableName[$i] == '_')
        {
            $tableName = substr($tableName, 0, $i) . strtoupper($tableName[$i + 1]) . substr($tableName, $i + 2);
        }
    }
    return $tableName;
}

function getDTOName($tableName)
{
    $name = getClassName($tableName);
    if ($name[strlen($name) - 1] == 's')
    {
        $name = substr($name, 0, strlen($name) - 1);
    }
    return $name;
}

function getVarName($tableName)
{
    $tableName = strtolower($tableName[0]) . substr($tableName, 1);
    for ($i = 0; $i < strlen($tableName); $i++)
    {
        if ($tableName[$i] == '_')
        {
            $tableName = substr($tableName, 0, $i) . strtoupper($tableName[$i + 1]) . substr($tableName, $i + 2);
        }
    }
    if ($tableName[strlen($tableName) - 1] == 's')
    {
        $tableName = substr($tableName, 0, strlen($tableName) - 1);
    }
    return $tableName;
}


function getVarNameWithS($tableName)
{
    $tableName = strtolower($tableName[0]) . substr($tableName, 1);
    for ($i = 0; $i < strlen($tableName); $i++)
    {
        if ($tableName[$i] == '_')
        {
            $tableName = substr($tableName, 0, $i) . strtoupper($tableName[$i + 1]) . substr($tableName, $i + 2);
        }
    }
    return $tableName;
}

?>