<?php
$classroom = $args['classroom'] ?? array();

$classroom_building_code = $classroom['Building Code'] ?? null;
$classroom_room_number   = $classroom['Room Number'] ?? null;
$classroom_title         = $classroom_building_code . ' ' . $classroom_room_number;
$classroom_building_name = $classroom['Building Name'] ?? null;
$classroom_capacity      = $classroom['Capacity'] ?? null;
$classroom_layout_type   = $classroom['Formatted_Room_Layout_Type'] ?? null;
$classroom_furniture     = $classroom['Formatted_Furniture'] ?? null;

if ( $classroom_layout_type && $classroom_furniture ) {
	$classroom_style_layout = $classroom_furniture . '; ' . $classroom_layout_type;
} elseif ( $classroom_layout_type ) {
	$classroom_style_layout = $classroom_layout_type;
} elseif ( $classroom_furniture ) {
	$classroom_style_layout = $classroom_furniture;
} else {
	$classroom_style_layout = null;
}

$classroom_slug = $classroom['Slug'] ?? null;
if ( '-' === $classroom_slug ) {
	$classroom_slug = null;
}

$classroom_thumbnail = $classroom['Image Gallery'][0] ?? null;

$classroom_thumbnail_string = null;

if ( isset( $classroom_thumbnail['url'] ) ) {
	$classroom_thumbnail_string  = '<img src="' . $classroom_thumbnail['url'] . '"';
	$classroom_thumbnail_string .= isset( $classroom_thumbnail['width'] ) ? ' width="' . $classroom_thumbnail['width'] . '"' : '';
	$classroom_thumbnail_string .= isset( $classroom_thumbnail['height'] ) ? ' height="' . $classroom_thumbnail['height'] . '"' : '';
	$classroom_thumbnail_string .= $classroom_title ? ' alt="' . $classroom_title . '"' : '';
	$classroom_thumbnail_string .= '>';
}

?>

<div class="classroom-card d-flex flex-column flex-md-row pt-5 pt-md-0 ps-md-5 position-relative">
	<div class="accent position-absolute"></div>

	<?php if ( $classroom_thumbnail_string ) { ?>
		<div class="classroom-thumbnail">
			<?php echo wp_kses_post( $classroom_thumbnail_string ); ?>
		</div>
	<?php } else { ?>
		<div class="classroom-thumbnail no-image"></div>
	<?php } ?>

	<div class="classroom-details p-5 ps-md-0 ms-md-9 flex-grow-1">
		<div class="d-flex align-items-start justify-content-between mb-5">
			<div>
				<h2 class="mb-0 fw-bold text-uppercase"><?php echo wp_kses_post( $classroom_title ); ?></h2>
				<div class="classroom-building-name fw-bold text-uppercase mt-2"><?php echo wp_kses_post( $classroom_building_name ); ?></div>
			</div>
			<a href="<?php echo esc_url( get_bloginfo( 'url' ) . '/classrooms/' . $classroom_slug ); ?>" class="btn btn-secondary ms-5 text-nowrap vpfo-classroom-link"><?php esc_html_e( 'View Space', 'ubc-vpfo-spaces-pages' ); ?></a>
		</div>

		<?php
		if ( $classroom_capacity || $classroom_style_layout ) {
			?>
			<div class="d-flex align-items-start">
				<?php
				if ( $classroom_capacity ) {
					?>
					<dl>
						<dt><?php esc_html_e( 'Capacity', 'ubc-vpfo-spaces-pages' ); ?></dt>
						<dd><?php echo wp_kses_post( $classroom_capacity ); ?></dd>
					</dl>
					<?php
				}
				if ( $classroom_style_layout ) {
					?>
					<dl class="ms-9">
						<dt><?php esc_html_e( 'Style &amp; Layout', 'ubc-vpfo-spaces-pages' ); ?></dt>
						<dd><?php echo wp_kses_post( $classroom_style_layout ); ?></dd>
					</dl>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>

<?php
unset(
	$building_classroom,
	$classroom,
	$classroom_building_code,
	$classroom_room_number,
	$classroom_title,
	$classroom_building_name,
	$classroom_capacity,
	$classroom_layout,
	$classroom_slug,
	$classroom_thumbnail_object,
	$classroom_thumbnail,
	$classroom_thumbnail_string
);
?>