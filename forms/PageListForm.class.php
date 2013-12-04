<?php
/**
 * PageListForm is used for queries and any page form that should return a list with different
 * pages (like a full client listing, or search results).  The itemsPerPage can be altered to
 * display more or less items per page.
 *
 * Normally when using a model, the code is thus:
 * model->performQueryAll(pageListForm);
 * $count = model->performCountAll(pageListForm)
 * pageListForm->setTotal($count);
 *
 * Then you would save the pageListForm to the request and forward on to the page.
 */
class PageListForm extends Form {

		private $total;
		private $items_per_page;
		private $keywords;
		private $page;
		private $sort;
		private $sord;
		private $ignore_pagination;

		/**
		 * constants
		 */
		const ITEMS_PER_PAGE = 20;
		
		/**
		* Returns the total number of results returned
		* @return integer
		*/
		function getTotal() {
			if (is_null($this->total)) {
				$this->total = 0;
			}
			return $this->total;
		}

		/**
		* Sets the total number of results
		* @param integer $arg0
		* @return void
		*/
		function setTotal($arg0) {
			$this->total=$arg0;
			return $this;
		}

		/**
		* Returns the page number
		* @return integer
		*/
		function getPage() {
			if (is_null($this->page)) {
				$this->page= 1;
			}
			if ($this->getTotal() > 0) {
				if($this->page > $this->getPageCount()) {
					$this->page = $this->getPageCount();
				}
			}
			if($this->page < 1) {
				$this->page = 1;
			}
			return $this->page;
		}

		/**
		* Sets the page number
		* @param integer $arg0
		*/
		function setPage($arg0) {
			$this->page=$arg0;
			return $this;
		}

		/**
		* Returns the keywords used in searches
		* @return string
		*/
		function getKeywords() {
			if (is_null($this->keywords)) {
				$this->keywords= "";
			}
			return $this->keywords;
		}

		/**
		* Sets the keywords used in searches
		* @param string $arg0
		*/
		function setKeywords($arg0) {
			$this->keywords=$arg0;
			return $this;
		}
		
		/**
		 * Returns the ignore_pagination
		 * @return boolean
		 */
		function getIgnorePagination() {
			if (is_null($this->ignore_pagination)) {
				$this->ignore_pagination = false;
			}
			return $this->ignore_pagination;
		}
		
		/**
		 * Sets the ignore_pagination
		 * @param $arg0 boolean
		 */
		function setIgnorePagination($arg0) {
			$this->ignore_pagination = $arg0;
			return $this;
		}

		/**
		* Returns the items to display per page
		* @return integer
		*/
		function getItemsPerPage() {
			if (is_null($this->items_per_page)) {
				if (defined("MO_ITEMS_PER_PAGE")) {
					$this->items_per_page = MO_ITEMS_PER_PAGE;
				} else {
					$this->items_per_page = self::ITEMS_PER_PAGE;
				}
			}
			return $this->items_per_page;
		}

		/**
		* Sets the items to display per page
		* @param integer $arg0
		*/
		function setItemsPerPage($arg0) {
			$this->items_per_page = $arg0;
			return $this;
		}

		/**
		* Returns the start index based on the current page number and the items per page
		* @return integer
		*/
		function getStartIndex() {
			return (($this->getPage() - 1) * $this->getItemsPerPage());
		}

		/**
		* Returns the total number of pages that should be returned based on the total and
		* items per page.
		* @return integer
		*/
		function getPageCount() {
			if ($this->getTotal() % $this->getItemsPerPage() == 0) {
				return (integer)($this->getTotal()/$this->getItemsPerPage());
			} else {
				return (integer)($this->getTotal()/$this->getItemsPerPage() + 1);
			}
		}

		/**
		* Returns the number for the next page.  If there isn't a next page it returns false
		* @return mixed
		*/
		function getNextPage() {
		   if($this->getPage() < $this->getPageCount()) {
		      return $this->getPage() + 1;
		   } else {
		      return false;
		   }
		}
		
		/**
		* Returns the number for the previous page.  If there isn't a previous page it return false.
		* @return mixed
		*/
		function getPreviousPage() {
			if($this->getPage() > 1) {
		      return $this->getPage() - 1;
		   } else {
		      return false;
		   }
		}

		/**
		 * returns the sort
		 * @return string
		 */
		function getSort() {
		   if (is_null($this->sort)) {
		      $this->sort = 1;
		   }
		   return $this->sort;
		}

		/**
		 * sets the sort
		 * @param string $arg0
		 */
		function setSort($arg0) {
		    $this->sort = StringTools::removeBadMySQLChars($arg0);
		    return $this;
		}

		/**
		 * Returns the sord
		 * @return string
		 */
		function getSord() {
			if (is_null($this->sord)) {
				$this->sord = 'DESC';
			}
			if (!(strtoupper(trim($this->sord)) == 'DESC' || strtoupper(trim($this->sord)) == 'ASC')) {
				$this->sord = 'DESC';
			}
			return $this->sord;
		}
		
		/**
		 * Sets the sord
		 * @param $arg0 string
		 */
		function setSord($arg0) {
			$this->sord = $arg0;
			return $this;
		}
	}
?>