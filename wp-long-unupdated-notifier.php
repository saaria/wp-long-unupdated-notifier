<?php
/*
  Plugin Name: WP Long Unupdated Notifier
  Plugin URI: https://github.com/saaria/wp-long-unupdated-notifier
  Description: The user is notified at the beginning of the article body that the content of the article is out of date.
  Version: 1.0.0
  Author: YUKI
  Author URI: https://github.com/saaria/
  License: GPLv2
 */

$gbSetting = null;

define('DEFAULT_MESSAGE', getMessage());
const DEFAULT_COLOR_TYPE = 'primary';
const DEFAULT_LAPSED_YEARS = 1;

function getMessage() {
	$lang = ($http_langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'])
	? explode( ',', $http_langs )[0] : 'en';
	if ( $lang === 'ja' ) {
		return 'この記事は更新から'.DEFAULT_LAPSED_YEARS.'年以上経過しています。情報が古い可能性がありますのでご注意下さい。';
	} else {
		$plural = DEFAULT_LAPSED_YEARS > 1 ? 's' : ''; 
		return 'This article has been over '.DEFAULT_LAPSED_YEARS.' year'.$plural.' since it was last updated. Please note that the information may be out of date.';
	}
}

add_action( 'admin_menu', 'add_plugin_setting' );

function add_plugin_setting() {
	add_options_page(
		'Setting | Long Unupdated Notifier',
		'Long Unupdated Notifier',
		'administrator',
		'setup_page',
		'notifytxt_setting_htmlpage'
	);
}

function notifytxt_setting_htmlpage() {
	global $gbSetting;
  
	$gbSetting = get_option( 'long_unupdated_notifier_setting' );
	if( !$gbSetting ) {
		$gbSetting = array(
			'message' => DEFAULT_MESSAGE,
			'colorType' => DEFAULT_COLOR_TYPE,
			'lapsedYears' => DEFAULT_LAPSED_YEARS,
		);
		update_option( 'long_unupdated_notifier_setting', $gbSetting );
	}
?>
	<div>
	<h2>Long Unupdated Notifier</h2>
	<form method="post" action="options.php">
<?php
    settings_fields( 'option_group' );
    do_settings_sections( 'setup_page' );
    submit_button();
?>
	</form>
	</div>
<?php
} 

add_action( 'admin_init', 'plugin_setting_init');

function plugin_setting_init() {
	register_setting(
		'option_group',
		'long_unupdated_notifier_setting'
	);		

	add_settings_section(
		'setting_section_id',
		'Setting',
		'print_sction_info',
		'setup_page'
	);

	add_settings_field(
		'output_message',
		'Output message:',
		'output_message_callback',
		'setup_page',
		'setting_section_id'
  );

	add_settings_field(
		'output_color_style',
		'Output color style:',
		'output_color_style_callback',
		'setup_page',
		'setting_section_id'
  );

  add_settings_field(
		'lapsed_years',
		'Lapsed years:',
		'lapsed_years_callback',
		'setup_page',
		'setting_section_id'
  );

}

function print_sction_info() {
}

function output_message_callback() {
	global $gbSetting;
	$html = '<input type="textarea" name="long_unupdated_notifier_setting[message]">'.$gbSetting['message'].'</textarea>';
	echo $html;
}
function output_color_style_callback() {
	global $gbSetting;
	$select_tbl = array(
		array( 'val'=>'primary', 'label'=>'primary' ),
		array( 'val'=>'warning', 'label'=>'warning' ),
		array( 'val'=>'danger', 'label'=>'danger' ),
	);
	$html = "";
	foreach( $select_tbl as $r ) {
		$v = $r['val'];
		$l = $r['label'];
		$selected = $v == $gbSetting['colorType'] ? 'selected' : '';
		$html .= '<option value="'.$v.'" '.$selected.'>'.$l.'</option>';
	}
	$html = '$html = "<select name="long_unupdated_notifier_setting[colorType]">'.$html.'</select>";';
	echo $html;
}
function lapsed_years_callback() {
	global $gbSetting;
	$html = '<input type="text" name="long_unupdated_notifier_setting[lapsedYears]" value="'.$gbSetting['lapsedYears'].'">';
	echo $html;
}

function getMessageHtml() {
	return '<div class="alert alert-'.$gbSetting['colorType'].'" role="alert">'.$gbSetting['message'].'</div>';
}

/**
  * 投稿詳細ページでフィルターフックを使って本文前に独自の情報を追加
  *
  * @param string $content : 本文の内容.
  * @return string $content : 本文の内容.
  */
function my_add_entry_content( $content ) {
	global $post;
	$modifiedDate = get_the_modified_date( 'Y-m-d', $post->ID );
	$lapsedYear   = (string) $gbSetting['lapsedYears'];
	// 表示を改変する投稿タイプの条件分岐.
	if ( $modifiedDate !== FALSE && strtotime( $modifiedDate ) < strtotime( '-'.$lapsedYear.' year' ) ) {
		if ( get_post_type() === 'post' ) {
			// 本文の内容の前に要素を追加.
			$content = getMessageHtml().$content;
		}
	}
	// 本文の内容を返す.
	return $content;
}
add_filter(
	'the_content',
	'my_add_entry_content',
	8 // 表示する優先順位.
);