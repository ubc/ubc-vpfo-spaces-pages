<?php
$building_classroom = $args['building_classroom'];
$classroom          = json_decode(json_encode($building_classroom->fields), true);

$classroom_building_code = $classroom['Building Code'] ?? null;
$classroom_room_number   = $classroom['Room Number'] ?? null;
$classroom_title         = $classroom_building_code . ' ' . $classroom_room_number;
$classroom_building_name = $classroom['Building Name'] ?? null;
$classroom_capacity      = $classroom['Capacity'] ?? null;
$classroom_layout        = $classroom['Layout'] ?? 'Data Source In Progress'; // TODO - get real data and fallback
$classroom_slug          = $classroom['Slug'] ?? null;

$classroom_thumbnail = $classroom['Image Gallery'][0] ?? null;

$classroom_thumbnail_string  = isset( $classroom_thumbnail['url'] ) ? '<img src="' . $classroom_thumbnail['url'] . '"' : '';
$classroom_thumbnail_string .= isset( $classroom_thumbnail['width'] ) ? ' width="' . $classroom_thumbnail['width'] . '"' : '';
$classroom_thumbnail_string .= isset( $classroom_thumbnail['height'] ) ? ' height="' . $classroom_thumbnail['height'] . '"' : '';
$classroom_thumbnail_string .= $classroom_title ? ' alt="' . $classroom_title . '"' : '';
$classroom_thumbnail_string .= isset( $classroom_thumbnail['url'] ) ? '>' : '';
?>

<div class="classroom-card d-flex flex-column flex-md-row pt-5">
	<?php if ( $classroom_thumbnail_string && ! empty( $classroom_thumbnail_string ) ) { ?>
		<div class="classroom-thumbnail">
			<?php echo wp_kses_post( $classroom_thumbnail_string ); ?>
		</div>
	<?php } ?>

	<div class="classroom-details">
		<h2 class="classroom-title"><?php echo wp_kses_post( $classroom_title ); ?></h2>
		<div class="classroom-building-name"><?php echo wp_kses_post( $classroom_building_name ); ?></div>
		<div class="classroom-capacity"><?php echo wp_kses_post( $classroom_capacity ); ?> Capacity</div>
		<div class="classroom-layout"><?php echo wp_kses_post( $classroom_layout ); ?></div>
		<a href="<?php echo esc_url( get_bloginfo( 'url' ) . '/classrooms/' . $classroom_slug ); ?>" class="btn btn-primary"><?php esc_html_e( 'View Space', 'ubc-vpfo-spaces-pages' ); ?></a>
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