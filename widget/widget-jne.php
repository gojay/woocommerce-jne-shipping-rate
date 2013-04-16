<?php
class JNE_Widget extends WP_Widget
{
	public function __construct()
	{
		$widget_options = array(
			'classname' => 'JNE_Widget_class',
			'description' => 'Menampilkan pelacakan dan daftar ongkos JNE'
		);
		
		parent::__construct( __CLASS__, 'JNE Express Across Nation', $widget_options);
	}
	
	/* build the widget settings form */
	public function form($instance)
	{
		$defaults = array(
			'title' => 'Tracking JNE'
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title 		= $instance['title'];
		$show_jne 	= $instance['show_jne'];
		?>
		<p>Title: 
		<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /> 
		</p>
		<p>Show JNE in popup: 
		<input type="checkbox" name="<?php echo $this->get_field_name('show_jne'); ?>" <?php checked( $show_jne, 'on' ); ?> />
		</p>
		<?php
	}
	
	/* save the widget settings */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['show_jne'] = strip_tags( $new_instance['show_jne'] );
		
		return $instance;
	}
	
	/* display widget */
	public function widget( $args, $instance )
	{
		extract( $args );
		
		$title 	   = apply_filters( 'widget_title', $instance['title'] );
		$show_jne  = empty( $instance['show_jne'] ) ? false : true ;
		
		echo $before_widget;
		
		if ( !empty( $title ) ) { 
			echo $before_title . $title . $after_title; 
		}
		
		include( JNE_PLUGIN_TPL_DIR . '/widget.php' );
		
		echo $after_widget;
	}
}