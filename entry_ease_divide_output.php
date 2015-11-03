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
	if(is_page() || is_single()){
		global $more; $more = 0;
		$content  = get_the_content('');
		$content .= get_addtional_content();
		$more = 1;
		$content  .= get_the_content('',true);
	}
	return $content;
}

function get_addtional_content(){
	$html = "差し込みたいコンテンツやで！";
	return $html;
}

