<?php
/**
 * Available variables
 * 
 * $class: (string) class(es) to be added to switcher container
 * $default_currency (string): woocommerce default currency code
 * $default_label: (string) woocommerce default currency label
 * $default_symbol: (string) woocommerce default currency symbol
 * $currencies: (array) currencies list added by admin (associated array )
 * $currency: (string) current currency code
 * $show_currency: (boolean) whether to add currency symbol to switcher or not
 * $show_flag: (boolean) whether to add currency flag to switcher or not
 */

if ( count($currencies) ) {
	wp_enqueue_style('wccs_flags_style', WCCS_PLUGIN_URL . 'assets/lib/flag-icon/flag-icon.css', '', '1.0');
	wp_enqueue_style('wccs_main_css', WCCS_PLUGIN_URL . 'assets/frontend/themes/style_01/wcc-main.css', '', '1.0');
	wp_enqueue_style('wccs_theme1_css', WCCS_PLUGIN_URL . 'assets/frontend/themes/style_01/theme-01.css', '', '1.0');
				
	wp_enqueue_script('wccs_theme1_script', WCCS_PLUGIN_URL . 'assets/frontend/themes/style_01/wcc-main.js', array( 'jquery' ), '1.0');
	?>
	<div id="wcc-switcher-style-01" class="wcc-switcher-style-01 wcc-wrapper <?php echo esc_html($class); ?>">
		<div class="wcc-crnt-currency d-flex">
		<?php if ( !$currency ) { ?>
			<?php if ( $show_currency ) { ?>
			<span class="wcc-symbol">(<?php echo esc_html($default_symbol); ?>)</span>
			<?php } ?>
			<span class="wcc-name"><?php echo esc_html($default_currency); ?> <?php echo esc_html__($default_text, 'wccs'); ?></span>
			<?php if ( $show_flag && $default_currency_flag ) { ?>
			<span class="wcc-flag flag-icon flag-icon-<?php echo esc_html($default_currency_flag); ?>"></span>
			<?php } ?>
		<?php } else { ?>
			<?php if ( $show_currency && isset($currencies[$currency]['symbol']) && $currencies[$currency]['symbol'] ) { ?>
			<span class="wcc-symbol">(<?php echo esc_html($currencies[$currency]['symbol']); ?>)</span>
			<?php } ?>
			<span class="wcc-name"><?php echo esc_html($currency); ?></span>
			<?php if ( $show_flag && $currencies[$currency]['flag'] ) { ?>
			<span class="wcc-flag flag-icon flag-icon-<?php echo esc_attr($currencies[$currency]['flag']); ?>"></span>
			<?php } ?>
		<?php } ?>
		</div>
		<ul class="wcc-list">
		<?php if ( $currency ) { ?>
			<li class="d-flex" data-code="<?php echo esc_attr($default_currency); ?>">
				<?php if ( $show_currency ) { ?>
				<span class="wcc-symbol">(<?php echo esc_html($default_symbol); ?>)</span>
				<?php } ?>
				<span class="wcc-name"><?php echo esc_html($default_currency); ?> <?php echo esc_html__($default_text, 'wccs'); ?></span>
				<?php if ( $show_flag && $default_currency_flag ) { ?>
				<span class="wcc-flag flag-icon flag-icon-<?php echo esc_html($default_currency_flag); ?>"></span>
				<?php } ?>
			</li>
		<?php } ?>
		
		<?php
		foreach ( $currencies as $code => $info ) {
			if ( $code != $currency ) {
				?>
			<li class="d-flex" data-code="<?php echo esc_attr($code); ?>">
				<?php if ( $show_currency && isset($info['symbol']) && $info['symbol'] ) { ?>
				<span class="wcc-symbol">(<?php echo esc_html($info['symbol']); ?>)</span>
				<?php } ?>
				<span class="wcc-name"><?php echo esc_html($code); ?></span>
				<?php if ( $show_flag && $info['flag'] ) { ?>
				<span class="wcc-flag flag-icon flag-icon-<?php echo esc_html($info['flag']); ?>"></span>
				<?php } ?>
			</li>
				<?php
			}
		}
		?>
		</ul>
		<form class="wcc_switcher_form_01" method="post" action="" style="display: none;">
			<input type="hidden" id="custom_nonce" name="custom_nonce" value="<?php echo esc_html(wp_create_nonce('custom_nonce')); ?>">
			<input type="hidden" name="wcc_switcher" class="wcc_switcher" value="">
		</form>
	</div>    
	<?php
}
