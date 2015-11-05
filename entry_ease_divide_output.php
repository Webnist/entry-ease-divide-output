<?php
/**
	*	Plugin Name:	entry_ease_divide_output
	*	Description:	entry_ease_divide_output
	*	Version:	 	0.0.1
	*	Author:		hideokamoto
	*	License:	 	GPLv2
*/
add_filter( 'the_content'         , 'e_edo_add_content' );

function e_edo_add_content( $content ){
	if ( is_admin() )
		return $content;

	if ( is_page() || is_single() ) {
		$before = get_addtional_content();
		$after  = get_addtional_content( 'after' );
		if ( ! empty( $before ) )
			$content = preg_replace( '/(<span id="more-[0-9]+">.*?<\/span>)/', $before . '$1', $content );

		if ( ! empty( $after ) )
			$content = preg_replace( '/(<span id="more-[0-9]+">.*?<\/span>)/', '$1' . $after, $content );
	}
	return $content;
}

function get_addtional_content( $prepost = 'before' ) {
	$post_id   = get_the_ID();
	$option    = get_option( 'eedo_' . $prepost . '_default_text' );
	$post_meta = get_post_meta( $post_id, 'eedo_' . $prepost, true );
	$content   = ! empty( $post_meta ) ? $post_meta : $option;
	return $content;
}

// Admin
add_action( 'admin_menu', 'entry_ease_divide_output_admin_menu' );
function entry_ease_divide_output_admin_menu() {
	$public_post_types = wp_list_filter(
		get_post_types( array( 'public' => true ) ),
		array( 'attachment' ),
		'NOT'
	);
	$post_types        = get_option( 'eedo_post_types' );
	$post_types        = ! empty( $post_types ) ? $post_types : $public_post_types;

	add_menu_page(
		__( 'Entry Ease Dvide Output Settings', 'entry_ease_divide_output' ),
		__( 'Entry Ease Dvide Output', 'entry_ease_divide_output' ),
		'manage_options',
		'entry_ease_divide_output',
		'entry_ease_divide_output_settings_page',
		'dashicons-editor-insertmore'
	);

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'eedo_before',
			__( 'Before Text', 'entry_ease_divide_output' ),
			'entry_ease_divide_output_meta_box',
			$post_type,
			'normal',
			'default',
			array(
				'prepost' =>'before'
			)
		);
		add_meta_box(
			'eedo_after',
			__( 'After Text', 'entry_ease_divide_output' ),
			'entry_ease_divide_output_meta_box',
			$post_type,
			'normal',
			'default',
			array(
				'prepost' =>'after'
			)
		);
	}
}

function entry_ease_divide_output_settings_page() {
	echo '<div id="themes-options-wrap" class="wrap">' . "\n";
	echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>' . "\n";
	echo '<form method="post" action="options.php">' . "\n";
	settings_fields( 'entry_ease_divide_output' );
	do_settings_sections( 'entry_ease_divide_output' );
	echo '<div class="submit">' . "\n";
	submit_button();
	echo '</div>' . "\n";
	echo '</form>' . "\n";
	echo '</div>' . "\n";
}

add_action( 'admin_init', 'entry_ease_divide_output_fields' );
function entry_ease_divide_output_fields() {

	$post_types = wp_list_filter(
		get_post_types( array( 'public' => true ) ),
		array( 'attachment' ),
		'NOT'
	);
	add_settings_section(
		'general',
		__( 'General', 'entry_ease_divide_output' ),
		'',
		'entry_ease_divide_output'
	);

	add_settings_field(
		'entry_ease_divide_output_post_types',
		__( 'Active post types', 'entry_ease_divide_output' ),
		'entry_ease_divide_output_post_type_checkbox',
		'entry_ease_divide_output',
		'general',
		array(
			'name'   => 'eedo_post_types[]',
			'value'  => get_option( 'eedo_post_types' ),
			'option' => $post_types,
		)
	);

	add_settings_section(
		'before',
		__( 'Default for before', 'entry_ease_divide_output' ),
		'',
		'entry_ease_divide_output'
	);

	add_settings_field(
		'entry_ease_divide_output_before_default_text',
		__( 'Before Text', 'entry_ease_divide_output' ),
		'entry_ease_divide_output_textarea_field',
		'entry_ease_divide_output',
		'before',
		array(
			'name'  => 'eedo_before_default_text',
			'value' => get_option( 'eedo_before_default_text' ),
		)
	);

	add_settings_section(
		'after',
		__( 'Default for after', 'entry_ease_divide_output' ),
		'',
		'entry_ease_divide_output'
	);

	add_settings_field(
		'entry_ease_divide_output_after_default_text',
		__( 'After Text', 'entry_ease_divide_output' ),
		'entry_ease_divide_output_textarea_field',
		'entry_ease_divide_output',
		'after',
		array(
			'name'  => 'eedo_after_default_text',
			'value' => get_option( 'eedo_after_default_text' ),
		)
	);

}

// Post
function entry_ease_divide_output_meta_box( $post, $args ) {
	extract( $args );
	$id      = $id;
	$prepost = $args['prepost'];
	$post_id = $post->ID;
	$value   = get_post_meta( $post_id, $id, true );
	$args    = array(
		'name'  => $id,
		'value' => $value,
	);
	wp_nonce_field( wp_create_nonce(__FILE__), $prepost . '_nonce' );
	entry_ease_divide_output_textarea_field( $args );
}

// Field
function entry_ease_divide_output_textarea_field( $args ) {
	extract( $args );
	$id      = ! empty( $id ) ? $id : $name;
	$output  = '<input type="text" name="' . $name .'" id="' . $id .'" class="large-text" value="' . $value .'">' . "\n";
	echo $output;
}

function entry_ease_divide_output_post_type_checkbox( $args ) {
	extract( $args );

	$output       = '';
	$count        = 1;
	$option_count = count( $option );

	foreach ( $option as $key ) {
		$post_type_object = get_post_type_object( $key );
		$labels           = $post_type_object->labels;
		$label            = $labels->name;
		$checked          = ( ! empty( $value ) && in_array( $key, $value ) ) ? ' checked="checked"' : '';
		$output  .= '<label><input name="' . $name . '" type="checkbox" value="' . $key . '"' . $checked . '>' . $label . '</label>' . "\n";
		if ( $count < $option_count )
			$output  .= '<br>' . "\n";

		$count++;
	}

	echo $output;
}

// register_setting
add_filter( 'admin_init', 'entry_ease_divide_output_register_setting' );
function entry_ease_divide_output_register_setting() {
	register_setting( 'entry_ease_divide_output', 'eedo_post_types' );
	register_setting( 'entry_ease_divide_output', 'eedo_before_default_text' );
	register_setting( 'entry_ease_divide_output', 'eedo_after_default_text' );
}

add_action( 'save_post', 'save_entry_ease_divide_output_meta_box' );
function save_entry_ease_divide_output_meta_box( $post_id ) {
	global $post;
	$nonce = isset( $_POST['before_nonce'] ) ? $_POST['before_nonce'] : isset( $_POST['after_nonce'] ) ? $_POST['after_nonce'] : null;

	if ( ! wp_verify_nonce( $nonce, wp_create_nonce(__FILE__) ) )
		return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	if ( ! current_user_can( 'edit_post', $post->ID ) )
		return $post_id;

	if ( isset( $_POST['before_nonce'] ) && isset( $_POST['eedo_before'] ) ) {
		update_post_meta( $post_id, 'eedo_before', $_POST['eedo_before']);
	}
	if ( isset( $_POST['after_nonce'] ) && isset( $_POST['eedo_after'] ) ) {
		update_post_meta( $post_id, 'eedo_after', $_POST['eedo_after']);
	}
}
