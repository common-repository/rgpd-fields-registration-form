<?php
/*
Plugin Name: RGPD Fields Registration Form
Description: With this plugin you can add some extra fields on your default registration form of WordPress to adapt it to the GDPR
Version: 0.1
Author: Rubén Alonso
Author URI: https://miposicionamientoweb.es/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

//Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

// CSS

function rgpd_frf_css() {
    wp_register_style( 'rgpd-frf-style', plugins_url( '/rgpd-frf-style.css', __FILE__ ), '', '0.1' );
    wp_enqueue_style( 'rgpd-frf-style' );
}
add_action( 'wp_enqueue_scripts', 'rgpd_frf_css' );

// Field adding
add_action('register_form', 'new_rgpd_item_register_form');
function new_rgpd_item_register_form() {
	
	$privacy_url = esc_url(get_option('privacy_url_rgpd_frf'));
	$init_text = get_option('init_text_rgpd_frf');
	$responsible = get_option('responsible_rgpd_frf');
	$purpose = get_option('purpose_rgpd_frf');
	$legitimacy = get_option('legitimacy_rgpd_frf');
	$target = get_option('target_rgpd_frf');
	$rights = get_option('rights_rgpd_frf');
	?>
	
	<div class="rgpd_frf_check">
		<?php if(!empty($privacy_url)) { ?><input id="field_rgpd_yes" type="checkbox" name="rgpd_yes" value="yes" required=""> Acepto la <a href="<?php echo $privacy_url ?>" target="_blank" rel="noopener noreferrer">Política de Privacidad</a>
	</div><br><?php } ?>
	<div class="rgpd_frf_info">
		<?php if(!empty($init_text)) { echo $init_text ?><br><?php } ?>
		<?php if(!empty($responsible)) { ?><strong>Responsable » </strong> <?php echo $responsible ?><br><?php } ?>
		<?php if(!empty($purpose)) { ?><strong>Finalidad » </strong> <?php echo $purpose ?><br><?php } ?>
		<?php if(!empty($legitimacy)) { ?><strong>Legitimación » </strong> <?php echo $legitimacy ?><br><?php } ?>
		<?php if(!empty($target)) { ?><strong>Destinatarios » </strong> <?php echo $target ?><br><?php } ?>
		<?php if(!empty($rights)) { ?><strong>Derechos » </strong> <?php echo $rights ?><?php } ?>
	</div><br>
	<?php
}

// Field validation
add_filter( 'registration_errors', 'new_rgpd_item_register_validation', 10, 3 );
function new_rgpd_item_register_validation( $errors, $sanitized_user_login, $user_email ) {
	
	if ( empty( sanitize_text_field($_POST['rgpd_yes']) ) ) {
		$errors->add( 'empty_rgpd_yes', __( '<strong>ERROR</strong>: Debes leer y aceptar la política de privacidad', 'rgpd-frf' ) );
	}

	return $errors;
}

add_filter( 'learndash-registration-errors', 'new_rgpd_item_register_errors');
function new_rgpd_item_register_errors( $errors_conditions ) {
	
	$new_errors_conditions = array_merge($errors_conditions, array(
			'empty_rgpd_yes'   => __( 'Debes leer y aceptar la política de privacidad.', 'rgpd-frf' ),
		));

	return $new_errors_conditions;
}

//////////////////////
//// Admin ///////////
//////////////////////

function rgpd_frf_menu(){
	//to prevent users without the right permissions from accessing here 
	if (!current_user_can('manage_options'))
		wp_die (__('No tienes suficientes permisos para acceder a esta página.'));
	
    add_menu_page( 'RGPD Fields', 'RGPD Fields', 'manage_options', 'rgpd-frf', 'rgpd_frf_page_options', 'dashicons-forms' );
}
add_action('admin_menu', 'rgpd_frf_menu');

function rgpd_frf_page_options() {

	//to prevent users without the right permissions from accessing here 
	if (!current_user_can('manage_options'))
		wp_die (__('No tienes suficientes permisos para acceder a esta página.'));
?>

	<div class="wrap">
<?php 
		$settings_saved = false;
		$success_msg = '';
		$error_msg = '';

		$privacy_url = '';
		$init_text = '';
		$responsible = '';
		$purpose = '';
		$legitimacy = '';
		$target = '';
		$rights = '';
	
		//check save and verify nonce post
		if(rgpd_frf_check_save_verify_nonce_post()) {
			$settings_saved = true;

			$privacy_url = esc_url_raw($_POST['privacy_url_rgpd_frf']);
			$init_text = sanitize_text_field($_POST['init_text_rgpd_frf']);
			$responsible = sanitize_text_field($_POST['responsible_rgpd_frf']);
			$purpose = sanitize_text_field($_POST['purpose_rgpd_frf']);
			$legitimacy = sanitize_text_field($_POST['legitimacy_rgpd_frf']);
			$target = sanitize_text_field($_POST['target_rgpd_frf']);
			$rights = sanitize_text_field($_POST['rights_rgpd_frf']);
			
			if(!empty($privacy_url) && !wp_http_validate_url($privacy_url))
			{
				$error_msg = __('La URL de la política de privacidad no es una URL correcta.', 'rgpd-frf'); 
			}
			else
			{
				update_option('privacy_url_rgpd_frf', $privacy_url);
				update_option('init_text_rgpd_frf', $init_text);
				update_option('responsible_rgpd_frf', $responsible);
				update_option('purpose_rgpd_frf', $purpose);
				update_option('legitimacy_rgpd_frf', $legitimacy);
				update_option('target_rgpd_frf', $target);
				update_option('rights_rgpd_frf', $rights);

				$success_msg = __('Opciones guardadas correctamente.', 'rgpd-frf');
			}
		}

		if ( $settings_saved ) : ?>
				<?php if($error_msg!='') { ?><div class="error notice"><?php echo $error_msg; ?></div><?php }  ?>
				<?php if($success_msg!='') { ?><div class="updated notice"><?php echo $success_msg; ?></div><?php }  ?>
		<?php endif ?>
		
		<h1><?php _e( 'RGPD Fields Registration Form', 'rgpd-frf' ) ?></h1>
		<p style="width:80%;">Aquí puedes configurar la URL de tu política de privacidad y la capa informativa que se mostrará justo debajo del formulario de registro de tu WordPress y otros plugins que lo usen, como LearnDash.</p>
		<br>
	</div>

	<div class="wrap">
	
		<form method="post" action="">
			<p>
				<strong><?php _e( 'URL de la política de privacidad', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="privacy_url_rgpd_frf" style="width: 70%" value="<?php echo esc_url(get_option( 'privacy_url_rgpd_frf' )); ?>">
			</p>
			<br>
			<p>
				<strong><?php _e( 'Texto inicial (opcional)', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="init_text_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'init_text_rgpd_frf' ); ?>">
			</p>
			<p>
				<strong><?php _e( 'Responsable', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="responsible_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'responsible_rgpd_frf' ); ?>">
			</p>
			<p>
				<strong><?php _e( 'Finalidad', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="purpose_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'purpose_rgpd_frf' ); ?>">
			</p>
			<p>
				<strong><?php _e( 'Legitimación', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="legitimacy_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'legitimacy_rgpd_frf' ); ?>">
			</p>
			<p>
				<strong><?php _e( 'Destinatarios', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="target_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'target_rgpd_frf' ); ?>">
			</p>
			<p>
				<strong><?php _e( 'Derechos', 'rgpd-frf' ) ?></strong><br>
				<input type="text" name="rights_rgpd_frf" style="width: 70%" value="<?php echo get_option( 'rights_rgpd_frf' ); ?>">
			</p>
			<br>
			<p>
				<?php wp_nonce_field( 'save-rgpd-frf-form-nonce', 'save-rgpd-frf-form-nonce' ); ?>
				<input name="save-rgpd-frf-form" type="submit" value="<?php _e( 'Guardar', 'rgpd-frf' ) ?>" />
			</p>
		</form>
	</div>
	<div class="wrap-bottom">
		<br>
		<p>
		  Este plugin ha sido creado por Rubén Alonso (<a href="https://miposicionamientoweb.es/" target="_blank" rel="noopener noreferrer">miposicionamientoweb.es</a>) solo para cumplir con la adaptación legal RGPD en el <strong>formulario de registro de WordPress</strong> y otros plugins que lo usen, como LearnDash.
		</p>
		<p>
		  Para una adaptación legal <strong>completa</strong> de tu sitio web, te recomiendo los <a href="https://miposicionamientoweb.es/visitar/kitslegales" target="_blank" rel="noopener noreferrer">kits legales de Marina Brocca</a>, especialista en RGPD y marketing legal.
		</p>
	</div>
<?php
}

//check save and verify nonce post.
function rgpd_frf_check_save_verify_nonce_post() {
	if( isset( $_POST['save-rgpd-frf-form'] ) && check_admin_referer( 'save-rgpd-frf-form-nonce', 'save-rgpd-frf-form-nonce' ) ) {
		return true;
	}
	return false;
}

// plugin uninstallation
register_uninstall_hook( __FILE__, 'rgpd_frf_uninstall' );
function rgpd_frf_uninstall() {
    delete_option('privacy_url_rgpd_frf');
	delete_option('init_text_rgpd_frf');
	delete_option('responsible_rgpd_frf');
	delete_option('purpose_rgpd_frf');
	delete_option('legitimacy_rgpd_frf');
	delete_option('target_rgpd_frf');
	delete_option('rights_rgpd_frf');
}