<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

#namespace Doctrine::DBAL::Schema;

/**
 * xxx
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @version     $Revision$
 * @since       2.0
 */
class Doctrine_Schema_MsSqlSchemaManager extends Doctrine_Schema_SchemaManager
{
    public function __construct(Doctrine_Connection_Mssql $conn)
    {
        $this->_conn = $conn;
    }
    
    /**
     * create a new database
     *
     * @param string $name name of the database that should be created
     * @return void
     */
    public function createDatabase($name)
    {
        $name = $this->conn->quoteIdentifier($name, true);
        $query = "CREATE DATABASE $name";
        if ($this->conn->options['database_device']) {
            $query.= ' ON '.$this->conn->options['database_device'];
            $query.= $this->conn->options['database_size'] ? '=' .
                     $this->conn->options['database_size'] : '';
        }
        return $this->conn->standaloneQuery($query, null, true);
    }

    /**
     * drop an existing database
     *
     * @param string $name name of the database that should be dropped
     * @return void
     */
    public function dropDatabase($name)
    {
        $name = $this->conn->quoteIdentifier($name, true);
        return $this->conn->standaloneQuery('DROP DATABASE ' . $name, null, true);
    }

    /**
     * Override the parent method.
     *
     * @return string The string required to be placed between "CREATE" and "TABLE"
     *                to generate a temporary table, if possible.
     */
    public function getTemporaryTableQuery()
    {
        return '';
    }  
    /**
     * alter an existing table
     *
     * @param string $name         name of the table that is intended to be changed.
     * @param array $changes     associative array that contains the details of each type
     *                             of change that is intended to be performed. The types of
     *                             changes that are currently supported are defined as follows:
     *
     *                             name
     *
     *                                New name for the table.
     *
     *                            add
     *
     *                                Associative array with the names of fields to be added as
     *                                 indexes of the array. The value of each entry of the array
     *                                 should be set to another associative array with the properties
     *                                 of the fields to be added. The properties of the fields should
     *                                 be the same as defined by the Metabase parser.
     *
     *
     *                            remove
     *
     *                                Associative array with the names of fields to be removed as indexes
     *                                 of the array. Currently the values assigned to each entry are ignored.
     *                                 An empty array should be used for future compatibility.
     *
     *                            rename
     *
     *                                Associative array with the names of fields to be renamed as indexes
     *                                 of the array. The value of each entry of the array should be set to
     *                                 another associative array with the entry named name with the new
     *                                 field name and the entry named Declaration that is expected to contain
     *                                 the portion of the field declaration already in DBMS specific SQL code
     *                                 as it is used in the CREATE TABLE statement.
     *
     *                            change
     *
     *                                Associative array with the names of the fields to be changed as indexes
     *                                 of the array. Keep in mind that if it is intended to change either the
     *                                 name of a field and any other properties, the change array entries
     *                                 should have the new names of the fields as array indexes.
     *
     *                                The value of each entry of the array should be set to another associative
     *                                 array with the properties of the fields to that are meant to be changed as
     *                                 array entries. These entries should be assigned to the new values of the
     *                                 respective properties. The properties of the fields should be the same
     *                                 as defined by the Metabase parser.
     *
     *                            Example
     *                                array(
     *                                    'name' => 'userlist',
     *                                    'add' => array(
     *                                        'quota' => array(
     *                                            'type' => 'integer',
     *                                            'unsigned' => 1
     *                                        )
     *                                    ),
     *                                    'remove' => array(
     *                                        'file_limit' => array(),
     *                                        'time_limit' => array()
     *                                    ),
     *                                    'change' => array(
     *                                        'name' => array(
     *                                            'length' => '20',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 20,
     *                                            ),
     *                                        )
     *                                    ),
     *                                    'rename' => array(
     *                                        'sex' => array(
     *                                            'name' => 'gender',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 1,
     *                                                'default' => 'M',
     *                                            ),
     *                                        )
     *                                    )
     *                                )
     *
     * @param boolean $check     indicates whether the function should just check if the DBMS driver
     *                             can perform the requested table alterations if the value is true or
     *                             actually perform them otherwise.
     * @return void
     */
    public function alterTable($name, array $changes, $check = false)
    {
        foreach ($changes as $changeName => $change) {
            switch ($changeName) {
                case 'add':
                    break;
                case 'remove':
                    break;
                case 'name':
                case 'rename':
                case 'change':
                default:
                    throw new Doctrine_Export_Exception('alterTable: change type "' . $changeName . '" not yet supported');
            }
        }

        $query = '';
        if ( ! empty($changes['add']) && is_array($changes['add'])) {
            foreach ($changes['add'] as $fieldName => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $query .= 'ADD ' . $this->conn->getDeclaration($fieldName, $field);
            }
        }

        if ( ! empty($changes['remove']) && is_array($changes['remove'])) {
            foreach ($changes['remove'] as $fieldName => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $field_name = $this->conn->quoteIdentifier($fieldName, true);
                $query .= 'DROP COLUMN ' . $fieldName;
            }
        }

        if ( ! $query) {
            return false;
        }

        $name = $this->conn->quoteIdentifier($name, true);
        return $this->conn->exec('ALTER TABLE ' . $name . ' ' . $query);
    }

    /**
     * create sequence
     *
     * @param string $seqName name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
     * @param array     $options  An associative array of table options:
     *                          array(
     *                              'comment' => 'Foo',
     *                              'charset' => 'utf8',
     *                              'collate' => 'utf8_unicode_ci',
     *                          );
     * @return string
     */
    public function createSequence($seqName, $start = 1, array $options = array())
    {
        $sequenceName = $this->conn->quoteIdentifier($this->conn->getSequenceName($seqName), true);
        $seqcolName = $this->conn->quoteIdentifier($this->conn->options['seqcol_name'], true);
        $query = 'CREATE TABLE ' . $sequenceName . ' (' . $seqcolName .
                 ' INT PRIMARY KEY CLUSTERED IDENTITY(' . $start . ', 1) NOT NULL)';

        $res = $this->conn->exec($query);

        if ($start == 1) {
            return true;
        }

        try {
            $query = 'SET IDENTITY_INSERT ' . $sequenceName . ' ON ' .
                     'INSERT INTO ' . $sequenceName . ' (' . $seqcolName . ') VALUES ( ' . $start . ')';
            $res = $this->conn->exec($query);
        } catch (Exception $e) {
            $result = $this->conn->exec('DROP TABLE ' . $sequenceName);
        }
        return true;
    }

    /**
     * This function drops an existing sequence
     *
     * @param string $seqName      name of the sequence to be dropped
     * @return void
     */
    public function dropSequenceSql($seqName)
    {
        $sequenceName = $this->conn->quoteIdentifier($this->conn->getSequenceName($seqName), true);
        return 'DROP TABLE ' . $sequenceName;
    }
    
    /**
     * lists all database sequences
     *
     * @param string|null $database
     * @return array
     */
    public function listSequences($database = null)
    {
        $query = "SELECT name FROM sysobjects WHERE xtype = 'U'";
        $tableNames = $this->conn->fetchColumn($query);

        return array_map(array($this->conn->formatter, 'fixSequenceName'), $tableNames);
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableColumns($table)
    {
        $sql     = 'EXEC sp_columns @table_name = ' . $this->conn->quoteIdentifier($table, true);
        $result  = $this->conn->fetchAssoc($sql);
        $columns = array();

        foreach ($result as $key => $val) {
            $val = array_change_key_case($val, CASE_LOWER);

            if (strstr($val['type_name'], ' ')) {
                list($type, $identity) = explode(' ', $val['type_name']);
            } else {
                $type = $val['type_name'];
                $identity = '';
            }

            if ($type == 'varchar') {
                $type .= '(' . $val['length'] . ')';
            }

            $val['type'] = $type;
            $val['identity'] = $identity;
            $decl = $this->conn->dataDict->getPortableDeclaration($val);

            $description  = array(
                'name'      => $val['column_name'],
                'ntype'     => $type,
                'type'      => $decl['type'][0],
                'alltypes'  => $decl['type'],
                'length'    => $decl['length'],
                'fixed'     => $decl['fixed'],
                'unsigned'  => $decl['unsigned'],
                'notnull'   => (bool) (trim($val['is_nullable']) === 'NO'),
                'default'   => $val['column_def'],
                'primary'   => (strtolower($identity) == 'identity'),
            );
            $columns[$val['column_name']] = $description;
        }

        return $columns;
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableIndexes($table)
    {

    }

    /**
     * lists tables
     *
     * @param string|null $database
     * @return array
     */
    public function listTables($database = null)
    {
        $sql = "SELECT name FROM sysobjects WHERE type = 'U' AND name <> 'dtproperties' ORDER BY name";

        return $this->conn->fetchColumn($sql);
    }

    /**
     * lists all triggers
     *
     * @return array
     */
    public function listTriggers($database = null)
    {
        $query = "SELECT name FROM sysobjects WHERE xtype = 'TR'";

        $result = $this->conn->fetchColumn($query);

        return $result;
    }

    /**
     * lists table triggers
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableTriggers($table)
    {
        $table = $this->conn->quote($table, 'text');
        $query = "SELECT name FROM sysobjects WHERE xtype = 'TR' AND object_name(parent_obj) = " . $table;

        $result = $this->conn->fetchColumn($query);

        return $result;
    }

    /**
     * lists table views
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableViews($table)
    {
        $keyName = 'INDEX_NAME';
        $pkName = 'PK_NAME';
        if ($this->conn->getAttribute(Doctrine::ATTR_PORTABILITY) & Doctrine::PORTABILITY_FIX_CASE) {
            if ($this->conn->getAttribute(Doctrine::ATTR_FIELD_CASE) == CASE_LOWER) {
                $keyName = strtolower($keyName);
                $pkName  = strtolower($pkName);
            } else {
                $keyName = strtoupper($keyName);
                $pkName  = strtoupper($pkName);
            }
        }
        $table = $this->conn->quote($table, 'text');
        $query = 'EXEC sp_statistics @table_name = ' . $table;
        $indexes = $this->conn->fetchColumn($query, $keyName);

        $query = 'EXEC sp_pkeys @table_name = ' . $table;
        $pkAll = $this->conn->fetchColumn($query, $pkName);

        $result = array();

        foreach ($indexes as $index) {
            if ( ! in_array($index, $pkAll) && $index != null) {
                $result[] = $this->conn->formatter->fixIndexName($index);
            }
        }

        return $result;
    }

    /**
     * lists database views
     *
     * @param string|null $database
     * @return array
     */
    public function listViews($database = null)
    {
        $query = "SELECT name FROM sysobjects WHERE xtype = 'V'";

        return $this->conn->fetchColumn($query);
    }
    
}

?>