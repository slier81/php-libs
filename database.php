<?php

/**
 * Class Database
 * @author slier
 */

namespace utility;

class Database extends PDO
{

    protected $db = null;
    protected $bind = null;
    protected $query = null;
    protected $where = null;
    protected $order = null;
    protected $limit = null;
    protected $count = null;


    /**
     * @param $dsn mysql:host=localhost;dbname=db_name
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public function __construct( $dsn, $username, $password )
    {
        try{
            if( !preg_match( '#[a-zA-Z]+:host=(http://)?[a-zA-Z0-9.]+;dbname=[a-zA-Z0-9]+#', $dsn ) ){
                throw new \PDOException( 'Invalid dsn, dsn should be in the following format [dbtype:host=localhost;dbname=db_name]' );
            }
            $this->db = new \PDO( $dsn, $username, $password );
            $this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        }
        catch( \PDOException $e ){
            $msg = "Message: {$e->getMessage()}";
            $msg .= "\nFile:   {$e->getFile()}";
            $msg .= "\nLine:   {$e->getLine()}";
            echo nl2br( $msg );
            exit(); /* cause pdo is stupid , its not stopping execution flow on exception */
        }
    }


    /**
     * Run raw query
     *
     * @param $sql
     * @param null $bind
     * @return Database
     *
     * query('select * from user')
     * query('select * from user where age > ? and level > ? ', array( $age, $level ) )
     * query('select * from user where age > :age and level > :level ', array( ':age' => $age, ':level' => $level ) )
     */
    public function query( $sql, $bind = null )
    {
        $this->bind( $bind );
        $this->query = $sql;
        return $this;
    }


    /**
     * Query to select data
     *
     * @param $table
     * @param string $column
     * @return Database
     */
    public function select( $table, $column = '*' )
    {
        $column = ( is_string( $column ) ) ? $column : implode( ',', $column );
        $this->query = 'SELECT ' . $column . ' FROM ' . $table;
        return $this;
    }


    /**
     * Insert a value into a table
     *
     * @param $table
     * @param array $data
     * @param null $bind
     * @return Database
     *
     * insert('users', array( array( 'username'=> '?', 'password'=> '?' ) ), array( $username, $password ) )
     * insert('users', array( array( 'username'=> ':username', 'password'=> ':password' ) ), array( ':username' => $username, ':password' => $password ) )
     */
    public function insert( $table, $data = array(), $bind = null )
    {
        $columns = null;
        $values = null;
        $this->bind( $bind );

        foreach( $data as $key => $val ){
            $columns .= $key . ',';
            if( preg_match( '#(:.*|\?{1}|.*?\(.*?\))#', $val ) ){
                $values .= $val . ',';
            }
            else{
                $values .= '"' . $val . '"' . ',';
            }
        }

        $columns = '(' . trim( $columns, ',' ) . ')';
        $values = '(' . trim( $values, ',' ) . ')';

        $this->query = 'INSERT INTO ' . $table . $columns . ' VALUES ' . $values;
        return $this;
    }


    /**
     * Update a value in a table
     *
     * @param $table
     * @param array $data
     * @param null $bind
     * @return Database
     *
     * update('users', array( array( 'username'=> '?', 'password'=> '?' ) ), array( $username, $password ) )
     * update('users', array( array( 'username'=> ':username', 'password'=> ':password' ) ), array( ':username' => $username, ':password' => $password ) )
     */
    public function update( $table, $data = array(), $bind = null )
    {
        $segment = null;
        $this->bind( $bind );

        foreach( $data as $key => $val ){
            if( preg_match( '#(:.*|\?{1}|.*?\(.*?\))#', $val ) ){
                $segment .= $key . '=' . $val . ',';
            }
            else{
                $segment .= $key . '="' . $val . '",';
            }
        }

        $segment = substr( $segment, 0, -1 );
        $this->query = 'UPDATE ' . $table . ' SET ' . $segment;
        return $this;
    }


    /**
     * Delete a record from a table
     *
     * @param $table
     * @return Database
     */
    public function delete( $table )
    {
        $this->query = 'DELETE FROM ' . $table;
        return $this;
    }


    /**
     * Get total fo result of the query
     *
     * @return Database
     */
    public function totalrow()
    {
        $this->count = true;
        return $this;
    }


    /**
     * Setup where clause
     *
     * @param $where
     * @param mixed $bind
     * @return Database
     * @throws \PDOException
     *
     * where( "username = ? and password = ?", array( $username, $password ) )
     * where( "username = :username and password = :password", array( ':username' => $username, ':password' => $password ) )
     */
    public function where( $where, $bind = null )
    {
        if( preg_match( '/where/i', $this->query ) ){
            throw new \PDOException( 'There is a where clause already in the sql statement' );
        }
        else{
            $this->bind( $bind );
            $this->where = ' WHERE ' . $where;
            return $this;
        }
    }


    /**
     * Setup order by clause
     *
     * @param string $order sorting the result
     * @return Database
     *
     * orderby( 'date Asc')
     */
    public function orderby( $order )
    {
        $this->order = ' ORDER BY ' . $order;
        return $this;
    }


    /**
     * Setup limit clause
     *
     * @param $start
     * @param $limit
     * @return Database
     */
    public function limit( $start, $limit )
    {
        $this->limit = ' LIMIT ' . $start . ',' . $limit;
        return $this;
    }


    /**
     * Method to get last insert id from insert statement
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }


    /**
     * Execute the query
     *
     * @return void
     */
    public function execute()
    {
        try{
            $sql = $this->query . $this->where . $this->order . $this->limit;
            $stmt = $this->db->prepare( $sql );
            $stmt->execute( $this->bind );
            $count = $this->count; //cache this value cause if use directly, statement below will always make $this->count = null
            $this->query = $this->where = $this->order = $this->limit = $this->count = $this->bind = null;

            if( preg_match( '/^sel/i', trim( $sql ) ) ){
                return ( $count ) ? count( $stmt->fetchAll( PDO::FETCH_ASSOC ) ) : $stmt->fetchAll( PDO::FETCH_ASSOC );
            }
            else{
                return $stmt->rowCount();
            }
        }
        catch( PDOException $e ){
            $msg = "Message: {$e->getMessage()}";
            $msg .= "\nFile:   {$e->getFile()}";
            $msg .= "\nLine:   {$e->getLine()}";
            echo nl2br( $msg );
            exit();
        }
    }


    /**
     * Build bind parameter
     *
     * @param mixed $bind
     * @return void
     */
    protected function bind( $bind )
    {
        if( is_null( $this->bind ) ){
            $this->bind = array();
        }

        if( !empty( $bind ) ){
            if( is_array( $bind ) ){
                if( $this->isAssoc( $bind ) ){
                    foreach( $bind as $key => $val ){
                        $this->bind[$key] = $val;
                    }
                }
                else{
                    foreach( $bind as $key => $val ){
                        $this->bind[] = $val;
                    }
                }
            }
            else{
                $this->bind[] = $bind;
            }
        }
    }


    /**
     * Check if an array is an associative array
     *
     * @access protected
     * @param array $arr
     * @return boolean
     */
    protected function isAssoc( $arr )
    {
        foreach( array_keys( $arr ) as $key ){
            if( !is_int( $key ) ) return true;
        }
        return false;
    }


}


?>