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
		private $itemsPerPage;
		private $keywords;
		private $page;
		protected $sort;
		protected $sort_type;
		private $category;

		/**
		 * constants
		 */
		const ITEMS_PER_PAGE = 20;
		const SORT_TYPE_ASC = 0;
		const SORT_TYPE_DESC = 1;
		const SORT_TYPE_NONE = 2;
		const SORT_TYPE_MAX = 3;


		/**
		* Returns the total number of results returned
		* @return integer
		*/
		function getTotal() {
			if (is_null($this->total)) {
				$this->total= 0;
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
		}

		/**
		* Returns the items to display per page
		* @return integer
		*/
		function getItemsPerPage() {
			if (is_null($this->itemsPerPage)) {
				if (defined("MO_ITEMS_PER_PAGE")) {
					$this->itemsPerPage= MO_ITEMS_PER_PAGE;
				} else {
					$this->itemsPerPage= PageListForm::ITEMS_PER_PAGE;
				}
			}
			return $this->itemsPerPage;
		}

		/**
		* Sets the items to display per page
		* @param integer $arg0
		*/
		function setItemsPerPage($arg0) {
			$this->itemsPerPage=$arg0;
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
		}

		/**
		 * returns the sort_type
		 * @return string
		 */
		function getSortType() {
		   if (is_null($this->sort_type)) {
		      $this->sort_type = PageListForm::SORT_TYPE_ASC;
		   }
		   return $this->sort_type;
		}
		
		/**
		 * returns the opposite sort type
		 * @return string
		 */
		function getOtherSortType() {
			if ($this->getSortType() == self::SORT_TYPE_ASC) {
				return self::SORT_TYPE_DESC;
			} else {
		   		return self::SORT_TYPE_ASC;
			}
		}

		/**
		 * sets the sort_type
		 * @param string $arg0
		 */
		function setSortType($arg0) {
		    $this->sort_type = $arg0;
		}

		/**
		 * returns the category
		 * @return string
		 */
		function getCategory ()
		{
			if (is_null ($this->category))
			{
				$this->category = 0;
			}
			return $this->category;
		}

		/**
		 * sets the category
		 * @param string $arg0
		 */
		function setCategory ($arg0)
		{
			$this->category = $arg0;
		}

		/**
		 * translate the sort type
		 * @return string
		 */
		function translateSortType() {
		    return PageListForm::translateSortTypeById($this->getSortType());
		}

		/**
		 * translate the sort type
		 * @return string
		 */
		static function translateSortTypeById($arg0) {
			$retVal = '';
			switch($arg0) {
				case PageListForm::SORT_TYPE_ASC:
					$retVal = 'ASC';
					break;
				case PageListForm::SORT_TYPE_DESC:
					$retVal = 'DESC';
					break;
				case PageListForm::SORT_TYPE_NONE:
				case PageListForm::SORT_TYPE_MAX:
				default:
					$retVal = '';
					break;
			}
			return $retVal;
		}

		/**
		 * Returns an array of page neighbors
		 * @param int $numpages
		 * @return array
		 */
		function getPageNeighbors($numpages = 5) {
				$retVal = array();
			$page = $this->getPage();
			$tot = $this->getPageCount();

			if ($page - $numpages >= 1){
				for ($i = $page-$numpages; $i <= $page; $i++){
					$retVal[] = $i;
				}
			}
			elseif ($page >= 1){
				for ($i=1; $i <= $page; $i++){
					$retVal[] = $i;
				}
			}
			if ($page + $numpages <= $tot ){
				for ($i = 1; $i <= $numpages; $i++){
					$retVal[] = $page+$i;
				}
			}
			elseif ($page <= $tot){
				for ($i = 1; $i <= $tot-$page; $i++){
					$retVal[] = $page+$i;
				}
			}
			return $retVal;
		}

		/**
		 * Returns HTML markup for page navigation
		 *
		 * @return	string
		 * @author	Mark Hobson
		 */
		function getPageNavigation ()
		{
			$retval = '';

			if($this->getPageCount() > 1)
			{

				$skip = array ('module', 'action', 'page', 'cookies', 'MserveAdmin');

				$retval = "
				<script>
					function navigatePageList (page)
					{
						var form = document.forms['__page_list_form__'];
						form.elements['page'].value = page;
						form.submit();
					}
				</script>
				<form name=\"__page_list_form__\" method=\"" . $_SERVER['REQUEST_METHOD'] . "\" action=\"/index.php/" . $_REQUEST['module'] . "/" . $_REQUEST['action'] . ".html\">";

				foreach ($_REQUEST as $name=>$value)
				{
					if (!in_array ($name, $skip))
					{
						$retval .= "
				<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />";
					}
				}

				$retval .= "
				<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"100%\">
					<tr>
						<td class=\"header2\" align=\"left\" style=\"width:25%;\">";

				if($this->getPreviousPage())
				{
					$retval .= "<a href=\"javascript:navigatePageList('" . $this->getPreviousPage() . "');\" class=\"pagelink\">Previous Page</a>";
				}

				$retval .= "</td>
						<td class=\"header2\" align=\"center\" style=\"width:50%;\">
							Page <input type=\"text\" name=\"page\" maxlength=\"10\" style=\"width: 30px; text-align:center;\" value=\"" . he($this->getPage()) . "\" /> of " . $this->getPageCount() . "
						</td>
						<td class=\"header2\" align=\"right\" style=\"width:25%;\">";

				if($this->getNextPage())
				{
					$retval .= "<a href=\"javascript:navigatePageList('" . $this->getNextPage() . "');\" class=\"pagelink\">Next Page</a>";
				}

				$retval .= "</td>
					</tr>
				</table>
				</form>";

			}

			return $retval;
		}

	}
?>
