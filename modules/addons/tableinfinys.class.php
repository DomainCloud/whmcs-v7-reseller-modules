<?php
class INFINYS_table{
	public $current_page;
	public $items_per_page;
	public $limit_end;
	public $limit_start;
	public $num_pages;
	public $total_items;
	public $querystring;
	public $per_page_array; // item per page array
	protected $limit;
	protected $mid_range;
	protected $return;
	protected $get_per_page;
	private $_table_header = array();
	 private $rows = array();

	public function __construct($total=0, $mid_range=7, $per_page_array=array(10,25,50,100,"All"), $filters) {
		$this->total_items = (int) $total;

		$this->mid_range = (int) $mid_range;
		if($this->mid_range%2 == 0 Or $this->mid_range < 1) 
			exit("Unable to paginate: Invalid mid_range value (must be an odd integer >= 1)");

		if(!is_array($per_page_array)) 
			exit("Unable to paginate: Invalid per_page_array value");
		$this->per_page_array = $per_page_array;

		$this->items_per_page = (isset($_GET["per_page"])) ? $_GET["per_page"] : $this->per_page_array[0];
		$this->default_per_page = $this->per_page_array[0];


		if($this->items_per_page == "All") {
			$this->num_pages = 1;
		} else {
			if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->per_page_array[0];
			$this->num_pages = ceil($this->total_items/$this->items_per_page);
		}

		$this->current_page = (isset($_GET["page"])) ? (int) $_GET["page"] : 1 ;
		if($_GET) {
			$args = explode("&amp;",$_SERVER["QUERY_STRING"]);
			foreach($args as $arg) {
				$keyval = explode("=",$arg);
				if($keyval[0] != "page" && $keyval[0] != "per_page" && !in_array($keyval[0], $filters)) 
					$this->querystring .= "&" . $arg;
			}
		}
		if($_POST) {
			foreach($_POST as $key=>$val) {
				if($key != "page" && $key != "per_page") $this->querystring .= "&$key=$val";
			}
		}
		$this->return = "<ul class=\"pager\">";
		$this->return .= ($this->current_page > 1) ? "<li class=\"previous\"><a href=\"$_SERVER[PHP_SELF]?page=".($this->current_page-1)."&per_page=$this->items_per_page$this->querystring\">« Previous Page</a></li>" : "<li class=\"previous disabled\"><a href=\"#\">« Previous Page</a></li>";
		$this->start_range = $this->current_page - floor($this->mid_range/2);
		$this->end_range = $this->current_page + floor($this->mid_range/2);

		if($this->start_range <= 0) {
			$this->end_range += abs($this->start_range)+1;
			$this->start_range = 1;
		}

		if($this->end_range > $this->num_pages) {
			$this->start_range -= $this->end_range-$this->num_pages;
			$this->end_range = $this->num_pages;
		}

		$this->range = range($this->start_range,$this->end_range);
		
		$this->return .= (($this->current_page < $this->num_pages) And ($this->items_per_page != "All") And $this->current_page > 0) ? "<li class=\"next\"><a href=\"$_SERVER[PHP_SELF]?page=".($this->current_page+1)."&per_page=$this->items_per_page$this->querystring\">Next Page »</a></li>" : "<li class=\"next disabled\"><a href=\"#\">Next Page »</a></li>";

		$this->return .= "</ul>";
		$this->return = str_replace("&","&amp;",$this->return);
		$this->limit_start = ($this->current_page <= 0) ? 0:($this->current_page-1) * $this->items_per_page;

		if($this->current_page <= 0) $this->items_per_page = 0;
		$this->limit_end = ($this->items_per_page == "All") ? (int) $this->total_items: (int) $this->items_per_page;
	}

	public function displayItemsPerPage() {
		$items = NULL;
		natsort($this->per_page_array); 
		foreach($this->per_page_array as $per_page_opt) $items .= ($per_page_opt == $this->items_per_page) ? "<option selected value=\"$per_page_opt\">$per_page_opt</option>\n":"<option value=\"$per_page_opt\">$per_page_opt</option>\n";
		return "<span class=\"paginate\">Items per page: </span><select class=\"\" onchange=\"window.location='$_SERVER[PHP_SELF]?page=1&amp;per_page='+this[this.selectedIndex].value+'$this->querystring';return false\">$items</select>\n";
	}

	public function displayJumpMenu() {
		$option=NULL;
		for($i=1;$i<=$this->num_pages;$i++) {
			$option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
		}
		return "<span class=\"paginate\">Page: </span><select class=\"\" onchange=\"window.location='$_SERVER[PHP_SELF]?page='+this[this.selectedIndex].value+'&amp;per_page=$this->items_per_page$this->querystring';return false\">$option</select>\n";
	}

	public function displayPages() {
		return $this->return;
	}

	public function setTableHeader($table_header){
		if (!is_array($table_header)) {
			return false;
		}
    	$this->_table_header = $table_header;
  	}

  	public function getTableHeader() {
  		$return = "<tr>";
		$columns = $this->_table_header;
		foreach ($columns as $column) {
			$return .= "<th>".$column."</th>";
		}
		$return .= "</tr>";
		return $return;
  	}

  	public function addRow($array) {
		if (!is_array($array)) {
			return false;
		}

		$this->rows[] = $array;
		return true;
	}

	public function getRows() {
		return $this->rows;
	}

	public function displayTable() {
		$return = ' 
		<style>
			a.link {
    			text-decoration: underline!important;
			}
		</style>
		<table id="sortabletbl0" class="datatable" width="100%" border="0" cellspacing="1" cellpadding=“3">';
		$return .= $this->getTableHeader();
		
		$rows = $this->getRows();
		$totalcols = count($this->_table_header);
		if (count($rows)) {
			foreach ($rows as $vals) {
				$return .= "<tr>";
				foreach ($vals as $val) {
					$return .= "<td>" . $val . "</td>";
				}

				$return .= "</tr>";
			}
		}
		else {
			$return .= "<tr><td colspan=\"" . $totalcols . "\">No records found.</td></tr>";
		}

		$return .= "</table></div>";
		return $return;
	}

	public function displayTableFooter() {
		if ($this->total_items > 0) {
			return "<p class=\"paginate\">$this->total_items Records Found, Page $this->current_page of $this->num_pages";
		}
		return false;
	}
}