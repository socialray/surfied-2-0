<?php

/**
 * Creates a toggle control
 */
class Optimizer_Controls_Info_Control extends WP_Customize_Control {

	public $type = 'info';

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>
		<label>
		<?php if ( !empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		</label>
        
		<?php if ( !empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif; ?>
        
        <?php if ( !$this->value() ) : ?>
        	<?php echo $this->value(); ?>
		<?php endif; ?>
        
		<?php
	}
}
