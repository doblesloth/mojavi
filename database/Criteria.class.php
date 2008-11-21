<?php

/**
 * Class for querying data from the database
 */
class Criteria
{

	/* Constants */
	const EQUAL				= '=';
	const NOT_EQUAL			= '<>';
	const ALT_NOT_EQUAL		= '!=';
	const GREATER_THAN		= '>';
	const LESS_THAN			= '<';
	const GREATER_EQUAL		= '>=';
	const LESS_EQUAL		= '<=';
	const LIKE				= ' LIKE ';
	const NOT_LIKE			= ' NOT LIKE ';
	const ILIKE				= ' ILIKE ';
	const NOT_ILIKE			= ' NOT ILIKE ';
	const IN				= ' IN ';
	const NOT_IN			= ' NOT IN ';
	const ASC				= 'ASC';
	const DESC				= 'DESC';
	const IS_NULL			= ' IS NULL ';
	const IS_NOT_NULL		= ' IS NOT NULL ';

	const TYPE_STRING			= 'setString';
	const TYPE_INTEGER			= 'setInt';
	const TYPE_FLOAT			= 'setFloat';
	const TYPE_LONG				= 'setLong';
	const TYPE_DATE				= 'setDate';
	const TYPE_TIME				= 'setTime';
	const TYPE_TIMESTAMP		= 'setTimestamp';
	const TYPE_NULL				= 'setNull';
	const TYPE_BOOLEAN			= 'setBoolean';
	const TYPE_ARRAY			= 'setArray';
	const TYPE_BARE_STRING		= 'setBareString';
	const TYPE_UNESCAPED_STRING	= 'setUnescapedString';
	const TYPE_BINARY_STRING	= 'setBinaryString';

	/* Main Vars */
	private $criterion		= array();
	private $selectColumns	= array();
	private $fromTables		= array();
	private $joins			= array();
	private $orderByColumns	= array();
	private $groupByColumns	= array();
	private $ignoreCase		= false;
	private $having;
	private $limit;
	private $offset;

	/********************\
	*   PUBLIC METHODS   *
	\********************/

	/**
	 * Add a column to the criteria
	 * @param	string Column name
	 * @param	mixed Value of column
	 * @param	string Type of column
	 * @param	string Comparison operator
	 * @return	void
	 */
	public function add ($column, $value, $type = self::TYPE_STRING, $comparison = self::EQUAL)
	{
		$this->criterion[] = new Criterion($column, $value, $type, $comparison);
		if (strpos($column, '.'))
		{
			list ($table, $column) = explode ('.', $column);
			$this->addFromTable($table);
		}
	}

	/**
	 * Get the internal criterion
	 * @return	array
	 */
	public function getCriterion ()
	{
		return $this->criterion;
	}

	/**
	 * Get a new instance of a Criterion
	 * @param	string Column name
	 * @param	mixed Value of column
	 * @param	string Type of column
	 * @param	string Comparison operator
	 * @return	Criterion
	 */
	public function getNewCriterion ($column, $value, $type = self::TYPE_STRING, $comparison = Criteria::EQUAL)
	{
		return new Criterion ($this, $column, $value, $type, $comparison);
	}

	/**
	 * Gets the selectColumns field.
	 * @return	array
	 */
	public function getSelectColumns ()
	{
		if(is_null($this->selectColumns))
		{
			$this->selectColumns = array();
		}
		return $this->selectColumns;
	}
	
	/**
	 * Sets the selectColumns field.
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	public function setSelectColumns ($arg0, $override = true)
	{
		if ($override)
		{
			$this->selectColumns = $arg0;
		} else
		{
			foreach ($arg0 as $column)
			{
				$this->addSelectColumn($column);
			}
		}
	}

	/**
	 * Add a selectColumn
	 * @param	string
	 * @return	void
	 */
	public function addSelectColumn ($arg0)
	{
		$this->selectColumns[] = $arg0;
	}

	/**
	 * Gets the fromTables field.
	 * @return	
	 */
	public function getFromTables ()
	{
		if(is_null($this->fromTables))
		{
			$this->fromTables = array();
		}
		return $this->fromTables;
	}
	
	/**
	 * Sets the fromTables field.
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	public function setFromTables ($arg0, $override = true)
	{
		if ($override)
		{
			$this->fromTables = $arg0;
		} else
		{
			foreach ($arg0 as $table)
			{
				$this->addFromTable($table);
			}
		}
	}

	/**
	 * Add a fromTable
	 * @param	string
	 * @return	void
	 */
	public function addFromTable ($arg0)
	{
		if (!in_array($arg0, $this->getFromTables()))
		{
			$this->fromTables[] = $arg0;
		}
	}

	/**
	 * Add a join
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function addJoin ($left, $right)
	{
		$this->joins[] = array('left'=>$left, 'right'=>$right);
		
		if (strpos($left, '.'))
		{
			list ($table, $column) = explode ('.', $left);
			$this->addFromTable($table);
		}

		if (strpos($right, '.'))
		{
			list ($table, $column) = explode ('.', $right);
			$this->addFromTable($table);
		}
	}

	/**
	 * Gets the joins field.
	 * @return	
	 */
	public function getJoins ()
	{
		if(is_null($this->joins))
		{
			$this->joins = array();
		}
		return $this->joins;
	}

	/**
	 * Add a column to the ORDER BY
	 * @param	string Column column
	 * @param	string Order
	 * @return	void
	 */
	public function addOrderByColumn ($column, $order = self::ASC)
	{
		$this->orderByColumns[] = $column . ' ' . $order;
	}

	/**
	 * Get the internal orderByColumns
	 * @return	array
	 */
	public function getOrderByColumns ()
	{
		return $this->orderByColumns;
	}

	/**
	 * Add a column to the GROUP BY
	 * @return	void
	 */
	public function addGroupByColumn ($column)
	{
		$this->groupByColumns[] = $column;
	}

	/**
	 * Get the internal groupByColumns
	 * @return	array
	 */
	public function getGroupByColumns ()
	{
		return $this->groupByColumns;
	}

	/**
	 * Gets the having field.
	 * @return	string
	 */
	public function getHaving ()
	{
		return $this->having;
	}
	
	/**
	 * Sets the having field.
	 * @param	string
	 */
	public function setHaving ($arg0)
	{
		$this->having = $arg0;
	}

	/**
	 * Gets the limit field.
	 * @return	
	 */
	public function getLimit ()
	{
		return $this->limit;
	}
	
	/**
	 * Sets the limit field.
	 * @param	
	 */
	public function setLimit ($arg0)
	{
		$this->limit = $arg0;
	}

	/**
	 * Gets the offset field.
	 * @return	
	 */
	public function getOffset ()
	{
		return $this->offset;
	}
	
	/**
	 * Sets the offset field.
	 * @param	
	 */
	public function setOffset ($arg0)
	{
		$this->offset = $arg0;
	}

	/**
	 * Gets the ignoreCase field.
	 * @return	boolean
	 */
	public function getIgnoreCase ()
	{
		if(is_null($this->ignoreCase))
		{
			$this->ignoreCase = false;
		}
		return $this->ignoreCase;
	}
	
	/**
	 * Sets the ignoreCase field.
	 * @param	boolean
	 */
	public function setIgnoreCase ($arg0)
	{
		$this->ignoreCase = (bool) $arg0;
	}

	/**
	 * Gets the query based upon internal criterion.
	 * @return	string
	 */
	public function getQuery ()
	{
		// Setup Query
		$query = "
			SELECT " . join(',', $this->getSelectColumns()) . "
			FROM " . join(',', $this->getFromTables()) . "
			WHERE 1=1
		";

		// Append joins to query
		foreach ($this->getJoins() as $join)
		{
			if ($this->getIgnoreCase())
			{
				$join['left'] = $this->ignoreCase($join['left']);
				$join['right'] = $this->ignoreCase($join['right']);
			}

			$query .= "
				AND " . $join['left'] . " = " . $join['right'] . "
			";
		}

		// Append criteria to query
		foreach ($this->getCriterion() as $criterion)
		{
			if (in_array($criterion->getComparison(), array(Criteria::LIKE, Criteria::NOT_LIKE, Criteria::ILIKE, Criteria::NOT_ILIKE)))
			{
				$criterion->setValue("%" . $criterion->getValue() . "%");
				$criterion->setType(Criteria::TYPE_STRING);
			} else if (in_array($criterion->getComparison(), array(Criteria::IS_NULL, Criteria::IS_NOT_NULL)))
			{
				$criterion->setType(Criteria::TYPE_NULL);
			}

			$column		= ((strlen($criterion->getTable()) > 0) ? $criterion->getTable() . '.' . $criterion->getColumn() : $criterion->getColumn());
			$comparison	= $criterion->getComparison();
			$token		= '?';

			if ($this->getIgnoreCase())
			{
				$column = $this->ignoreCase($column);
				$token	= $this->ignoreCase($token);
			}

			$query .= "
				AND " . $column . $comparison . $token . "
			";
		}

		// Add GROUP BY
		if (sizeof($this->getGroupByColumns()) > 0)
		{
			$query .= "
				GROUP BY " . join(", ", $this->getGroupByColumns()) . "
			";
		}

		// Add HAVING
		if (!is_null($this->getHaving()))
		{
			$query .= "
				HAVING " . $this->getHaving() . "
			";
		}

		// Add ORDER BY
		if (sizeof($this->getOrderByColumns()) > 0)
		{
			$query .= "
				ORDER BY " . join(", ", $this->getOrderByColumns()) . "
			";
		}

		// Add LIMIT
		if (!is_null($this->getLimit()))
		{
			$query .= "
				LIMIT " . $this->getLimit() . "
			";
		}

		// Add OFFSET
		if (!is_null($this->getOffset()))
		{
			$query .= "
				OFFSET " . $this->getOffset() . "
			";
		}

		return $query;
	}

	/**
	 * Gets the PreparedStatement based upon internal criterion.
	 * @return	PreparedStatement
	 */
	public function getPreparedStatement ($query = null)
	{
		if (is_null($query))
		{
			// Setup Query
			$query = $this->getQuery();
		}

		// Setup PreparedStatement
		$ps = new PreparedStatement($query);

		// Add criteria to PreparedStatement
		foreach ($this->getCriterion() as $criterion)
		{
			$method = $criterion->getType();
			$ps->$method($criterion->getValue());
		}

		return $ps;
	}

	/*********************\
	*   PRIVATE METHODS   *
	\*********************/

	/**
	 * Return an SQL clause to convert value to UPPER
	 * @param	string
	 * @return	string
	 */
	private function ignoreCase ($v)
	{
		return 'UPPER(' . $v . ')';
	}

}

class Criterion
{
	/* Constants */
	// UND & ODER are to be used for nested Criterion,
	//	which is yet to be implemented.
	const UND	= ' AND ';
	const ODER	= ' OR ';

	/* Main Vars */
	private $table;
	private $column;
	private $value;
	private $type;
	private $comparison;

	/********************\
	*   PUBLIC METHODS   *
	\********************/

	/**
	 * Construct the object
	 * @return	void
	 */
	public function __construct ($column, $value, $type, $comparison)
	{
		// table.column
		if (strpos($column, '.'))
		{
			list ($this->table, $this->column) = explode ('.', $column);
		} else
		{
			$this->column = $column;
		}
		$this->value		= $value;
		$this->type			= $type;
		$this->comparison	= $comparison;
	}

	/**
	 * Gets the table field.
	 * @return	
	 */
	public function getTable ()
	{
		if(is_null($this->table))
		{
			$this->table = '';
		}
		return $this->table;
	}
	
	/**
	 * Sets the table field.
	 * @param	
	 */
	public function setTable ($arg0)
	{
		$this->table = $arg0;
	}

	/**
	 * Gets the column field.
	 * @return	string
	 */
	public function getColumn ()
	{
		if(is_null($this->column))
		{
			$this->column = '';
		}
		return $this->column;
	}
	
	/**
	 * Sets the column field.
	 * @param	string
	 */
	public function setColumn ($arg0)
	{
		$this->column = $arg0;
	}

	/**
	 * Gets the value field.
	 * @return	mixed
	 */
	public function getValue ()
	{
		if(is_null($this->value))
		{
			$this->value = '';
		}
		return $this->value;
	}
	
	/**
	 * Sets the value field.
	 * @param	string
	 */
	public function setValue ($arg0)
	{
		$this->value = $arg0;
	}

	/**
	 * Gets the type field.
	 * @return	string
	 */
	public function getType ()
	{
		if(is_null($this->type))
		{
			$this->type = Criteria::TYPE_STRING;
		}
		return $this->type;
	}
	
	/**
	 * Sets the type field.
	 * @param	string
	 */
	public function setType ($arg0)
	{
		$this->type = $arg0;
	}

	/**
	 * Gets the comparison field.
	 * @return	string
	 */
	public function getComparison ()
	{
		if(is_null($this->comparison))
		{
			$this->comparison = Criteria::EQUAL;
		}
		return $this->comparison;
	}	
	
	/**
	 * Sets the comparison field.
	 * @param	string
	 */
	public function setComparison ($arg0)
	{
		$this->comparison = $arg0;
	}

}

?>