<?php
/*
 * Plugin Name: Test List Table Example
 */
if (! class_exists ( 'WP_List_Table' )) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class My_Example_List_Table extends WP_List_Table {
	var $example_data = array (
			array (
					'ID' => 1,
					'booktitle' => 'Quarter Share',
					'author' => 'Nathan Lowell',
					'isbn' => '978-0982514542' 
			),
			array (
					'ID' => 2,
					'booktitle' => '7th Son: Descent',
					'author' => 'J. C. Hutchins',
					'isbn' => '0312384378' 
			),
			array (
					'ID' => 3,
					'booktitle' => 'Shadowmagic',
					'author' => 'John Lenahan',
					'isbn' => '978-1905548927' 
			),
			array (
					'ID' => 4,
					'booktitle' => 'The Crown Conspiracy',
					'author' => 'Michael J. Sullivan',
					'isbn' => '978-0979621130' 
			),
			array (
					'ID' => 5,
					'booktitle' => 'Max Quick: The Pocket and the Pendant',
					'author' => 'Mark Jeffrey',
					'isbn' => '978-0061988929' 
			),
			array (
					'ID' => 6,
					'booktitle' => 'Jack Wakes Up: A Novel',
					'author' => 'Seth Harwood',
					'isbn' => '978-0307454355' 
			) 
	);
	function __construct() {
		global $status, $page;
		parent::__construct ( array (
				'singular' => __ ( 'book', 'mylisttable' ), // singular name of the listed records
				'plural' => __ ( 'books', 'mylisttable' ), // plural name of the listed records
				'ajax' => false 
		) // does this table support ajax?
 );
		add_action ( 'admin_head', array (
				&$this,
				'admin_header' 
		) );
	}
	function admin_header() {
		$page = (isset ( $_GET ['page'] )) ? esc_attr ( $_GET ['page'] ) : false;
		if ('my_list_test' != $page)
			return;
		echo '<style type="text/css">';
		echo '.wp-list-table .column-id { width: 5%; }';
		echo '.wp-list-table .column-booktitle { width: 40%; }';
		echo '.wp-list-table .column-author { width: 35%; }';
		echo '.wp-list-table .column-isbn { width: 20%;}';
		echo '</style>';
	}
	function no_items() {
		_e ( 'No books found, dude.' );
	}
	function column_default($item, $column_name) {
		switch ($column_name) {
			case 'booktitle' :
			case 'author' :
			case 'isbn' :
				return $item [$column_name];
			default :
				return print_r ( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}
	function get_sortable_columns() {
		$sortable_columns = array (
				'booktitle' => array (
						'booktitle',
						false 
				),
				'author' => array (
						'author',
						false 
				),
				'isbn' => array (
						'isbn',
						false 
				) 
		);
		return $sortable_columns;
	}
	function get_columns() {
		$columns = array (
				'cb' => '<input type="checkbox" />',
				'booktitle' => __ ( 'Title', 'mylisttable' ),
				'author' => __ ( 'Author', 'mylisttable' ),
				'isbn' => __ ( 'ISBN', 'mylisttable' ) 
		);
		return $columns;
	}
	function usort_reorder($a, $b) {
		// If no sort, default to title
		$orderby = (! empty ( $_GET ['orderby'] )) ? $_GET ['orderby'] : 'booktitle';
		// If no order, default to asc
		$order = (! empty ( $_GET ['order'] )) ? $_GET ['order'] : 'asc';
		// Determine sort order
		$result = strcmp ( $a [$orderby], $b [$orderby] );
		// Send final sort direction to usort
		return ($order === 'asc') ? $result : - $result;
	}
	function column_booktitle($item) {
		$actions = array (
				'edit' => sprintf ( '<a href="?page=%s&action=%s&book=%s">Edit</a>', $_REQUEST ['page'], 'edit', $item ['ID'] ),
				'delete' => sprintf ( '<a href="?page=%s&action=%s&book=%s">Delete</a>', $_REQUEST ['page'], 'delete', $item ['ID'] ) 
		);
		return sprintf ( '%1$s %2$s', $item ['booktitle'], $this->row_actions ( $actions ) );
	}
	function get_bulk_actions() {
		$actions = array (
				'delete' => 'Delete' 
		);
		return $actions;
	}
	function column_cb($item) {
		return sprintf ( '<input type="checkbox" name="book[]" value="%s" />', $item ['ID'] );
	}
	function prepare_items() {
		$columns = $this->get_columns ();
		$hidden = array ();
		$sortable = $this->get_sortable_columns ();
		$this->_column_headers = array (
				$columns,
				$hidden,
				$sortable 
		);
		usort ( $this->example_data, array (
				&$this,
				'usort_reorder' 
		) );
		
		$per_page = 5;
		$current_page = $this->get_pagenum ();
		$total_items = count ( $this->example_data );
		// only ncessary because we have sample data
		$this->found_data = array_slice ( $this->example_data, (($current_page - 1) * $per_page), $per_page );
		$this->set_pagination_args ( array (
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page' => $per_page 
		) // WE have to determine how many items to show on a page
 );
		$this->items = $this->found_data;
	}
} // class
function my_add_menu_items() {
	$hook = add_menu_page ( 'My Plugin List Table', 'My List Table Example', 'activate_plugins', 'my_list_test', 'my_render_list_page' );
	add_action ( "load-$hook", 'add_options' );
}
function add_options() {
	global $myListTable;
	$option = 'per_page';
	$args = array (
			'label' => 'Books',
			'default' => 10,
			'option' => 'books_per_page' 
	);
	add_screen_option ( $option, $args );
	$myListTable = new My_Example_List_Table ();
}
add_action ( 'admin_menu', 'my_add_menu_items' );
function my_render_list_page() {
	global $myListTable;
	echo '</pre><div class="wrap"><h2>My List Table Test</h2>';
	$myListTable->prepare_items ();
	?>
<form method="post">
	<input type="hidden" name="page" value="ttest_list_table">
    <?php
	$myListTable->search_box ( 'search', 'search_id' );
	$myListTable->display ();
	echo '</form></div>';
}
