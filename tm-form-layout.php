<?php
/**
 * The template for displaying the Catch Your Job Contest Form
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
acf_form_head();
get_header();
$site_url = get_site_url();
if(isset($_GET['formname'])) {
    $form_name = $_GET['formname'];
}
?>
	<header class="page-header alignwide">
		<h1 class="page-title"><?php if($form_name) echo $form_name;?></h1>
	</header>

    <div class="page-content">

        <?php 
        if($form_name) {
            acf_form(array(
                'post_id'       => 'new_post',
                'new_post'      => array(
                    'post_type'     => $form_name,
                    'post_status'   => 'publish'
                ),
                'return'		=> get_site_url()."/tm-form?formname=${formname}submitted=true",
                'submit_value'  => 'Submit'
            )); 
        } else {
            echo 'No form Specified';
        }
        ?>

    </div><!-- .page-content -->

<?php
get_footer();
