<?php
/*
Plugin Name: Woocommerce bulk percentage pricing
Plugin URI: http://atastypixel.com/blog/wordpress/plugins/woocommerce-bulk-percentage-pricing/
Description: Instead of slow pricing methods, you can now automatically update your store prices for all products at once by percentage.
Version: 1.0
Author: Hussam Hussien
Author URI: http://hussam7ussien.blogspot.com/
*/

/*  Copyright 2015 Hussam Hussien <hussam7ussien@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/




/**
 * Set up administration
 *
 * @package bulk_percentage_pricing
 * @since 0.1
 */
function wbpp_bulk_percentage_pricing_setup_admin() {
	add_options_page( 'Bulk percentage pricing', 'Bulk percentage pricing', 5, __FILE__, 'wbpp_bulk_percentage_pricing_options_page' );
}

/**
 * Options page
 *
 * @package bulk_percentage_pricing
 * @since 0.1
 */
function wbpp_bulk_percentage_pricing_options_page() {
	wp_enqueue_script('wbpp_bootstrap', plugin_dir_url( __FILE__ ) . 'assets/bootstrap/js/bootstrap.js', array('jquery'));
	wp_enqueue_script('wbpp_bootstrap_tagsinput', plugin_dir_url( __FILE__ ) . 'assets/bootstrap-tagsinput/bootstrap-tagsinput.js', array('jquery'));	
	wp_register_script('wbpp_js', plugin_dir_url( __FILE__ ) . 'assets/javascript.js', array('jquery'));	
	wp_localize_script( 'wbpp_js', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
	wp_enqueue_script( 'wbpp_js' );
	wp_enqueue_style( 'wbpp_style', plugin_dir_url( __FILE__ ) . 'assets/style.css');
	wp_enqueue_style( 'wbpp_style_bootstrap', plugin_dir_url( __FILE__ ) . 'assets/bootstrap/css/bootstrap.css');
	wp_enqueue_style( 'wbpp_style_bootstrap_theme', plugin_dir_url( __FILE__ ) . 'assets/bootstrap/css/bootstrap-theme.css');
	wp_enqueue_style( 'wbpp_style_bootstrap_tagsinput', plugin_dir_url( __FILE__ ) . 'assets/bootstrap-tagsinput/bootstrap-tagsinput.css');
	$taxonomy = 'product_cat';
	$orderby = 'name';
	$show_count = 0; // 1 for yes, 0 for no
	$pad_counts = 0; // 1 for yes, 0 for no
	$hierarchical = 1; // 1 for yes, 0 for no
	$title = '';
	$empty = 0;

	$args = array(
	'taxonomy' => $taxonomy,
	'orderby' => $orderby,
	'show_count' => $show_count,
	'pad_counts' => $pad_counts,
	'hierarchical' => $hierarchical,
	'title_li' => $title,
	'hide_empty' => $empty
	);
	$all_categories = get_categories( $args );
	$all_products=array();
	$args = array(
	        'post_type' => 'product',
	        'posts_per_page' => -1
	    );
	    $loop = new WP_Query( $args );
	if ( $loop->have_posts() ): while ( $loop->have_posts() ): $loop->the_post();
	global $product;
	$all_products[$product->id]=$product->get_title();
	endwhile; endif; wp_reset_postdata();
		?>
	<div class="wrap">
		<div class="panel panel-default">
			<div class="panel-heading">
					<h3 class="panel-title">Choose between following methods :</h3>
			</div>
			<div class="panel-body">
				<ul class="nav nav-pills">
					<li role="presentation" class="pricing-mode-li active" name="all_products"><a href="#">All Products</a></li>
					<li role="presentation" class="pricing-mode-li" name="specific_categories"><a href="#">Categories</a></li>
					<li role="presentation" class="pricing-mode-li" name="specific_products"><a href="#">Specific Products</a></li>
				</ul>
			</div>
		</div>


	<div class="boxed mode-panel" id="specific_categories">
	<div class="select-container">
	<label>Pleas select category/categories to add</label>
	<select multiple id="add_categories">
	<?php
	foreach ($all_categories as $cat)
	{
			echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';

	}
	?>
	</select>
	<input type="submit" value=">>" name="add_categories" select="add_categories" class="add-elements">
	</div>
	<input type="text" name="add_categories_input"  id="add_categories_input" />
	</div>


	<div class="boxed mode-panel" id="specific_products">
	<label>Pleas select peoduct/products to add</label>
	<div class="select-container"> 
	<select multiple id="add_products" >
	<?php
	foreach ($all_products as $key => $product)
	{
		echo '<option value="'.$key.'">'.$product.'</option>';
	}
	?>
	</select>
	<input type="submit" value=">>" name="add_products" select="add_products" class="add-elements">
	</div>
	<input type="text"   name="add_products_input" id="add_products_input" />
	</div>
	<div class="boxed" id="percentage_form">
	<form method="post" action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>">
	<?php wp_nonce_field('update-prices'); ?>
	
	<table class="form-table">
	
	<tr valign="top">
		<th scope="row"><?php _e('Percentage:') ?><br/><small><?php _e('(Enter pricing percentage)') ?></small></th>
		<td>
			<input type="number" name="percentage" id="percentage" value="0" />%<br />
			
		</td>
	</tr>
	
	</table>
	<img src="<?php echo  plugin_dir_url( __FILE__ ) . 'assets/images/ajax-loader_2.gif'; ?>" id="loader">
	<p class="submit">
	<input type="submit" name="percentge-submit" id="percentge-submit" value="<?php _e('Apply Changes') ?>" />
	</p>
	</form>
	<div class="updated" style="display:none;">
		
	</div>
	</div>
	</div>
	<?php
}

function wbpp_apply_percentge() {	
		$response['response']="falied";
		//get operation type
		$operation=$_POST["operation"];
        // sanitize percentage value
        $percentage  =$_POST["percentage"];
		$args = array( 'post_type' => 'product', 'posts_per_page' => -1 );
        if($operation=="all_products"):
		$args = array( 'post_type' => 'product', 'posts_per_page' => -1 );
		elseif($operation=="specific_products"):
		$args['post__in']=$_POST['values'];
		else:
		$args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'id',
			'terms'    => $_POST['values'],
			),
			);
		endif;
		query_posts( $args );
		while ( have_posts() ) : the_post();
			$product = new WC_Product( get_the_ID() );
			$regular_price = $product->regular_price;
			$product_price = $product->price;
			if($percentage>=0):
			$new_regular_price=$regular_price+(($regular_price*$percentage )/100);
			$new_product_price=$product_price+(($product_price*$percentage )/100);
			elseif($percentage<0):
			$percentage*=-1;
			$new_regular_price=$regular_price-(($regular_price*$percentage )/100);
			$new_product_price=$product_price-(($product_price*$percentage )/100);
			endif;	
			update_post_meta(  get_the_ID(), '_regular_price', $new_regular_price );
			update_post_meta(  get_the_ID(), '_price', $new_product_price );
		endwhile;
	    header( "Content-Type: application/json" );
	    $response['response']="SUCCESS - Percentage : ".$percentage."% applied successfully";
	    echo json_encode($response);

	    //Don't forget to always exit in the ajax function.
	    exit();
    }



	function wbpp_apply_percentge1(){

	    $reponse = array();
	    if(!empty($_POST['operation'])){
	         $response['response'] = "I've get the operation a its value is ".$_POST['operation'].' and the plugin url is '.plugins_url();
	    } else {
	         $response['response'] = "You didn't send the operation";
	    }

	    header( "Content-Type: application/json" );
	    echo json_encode($response);

	    //Don't forget to always exit in the ajax function.
	    exit();

	}




// Set up plugin
add_action( 'admin_menu', 'wbpp_bulk_percentage_pricing_setup_admin' );
add_action('wp_ajax_wbpp_apply_percentge', 'wbpp_apply_percentge');

?>
