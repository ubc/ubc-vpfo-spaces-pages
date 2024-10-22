<?php
get_header();

$building        = $args['building'];
$building_fields = json_decode(json_encode($building->fields), true);

$building_name        = $building_fields['Building Name'][0] ?? null;
$building_code        = $building_fields['Building Code'][0] ?? null;
$building_name_w_code = $building_name && $building_code ? $building_name . ' - ' . $building_code : $building_name;

$breadcrumb_home         = get_bloginfo( 'url' );
$breadcrumb_find_a_space = get_page_by_path( 'find-a-space' ) !== null ? get_permalink( get_page_by_path( 'find-a-space' ) ) : null;
$breadcrumb              = '<a href="' . $breadcrumb_home . '" class="d-inline-block" title="' . get_bloginfo( 'name' ) . '" rel="bookmark">' . get_bloginfo( 'name' ) . '</a>';
$breadcrumb             .= $breadcrumb_find_a_space ? '<i class="fas fa-chevron-right mx-4"></i><a href="' . $breadcrumb_find_a_space . '" class="d-inline-block">' . __( 'Find a Space', 'ubc-vpfo-spaces-pages' ) . '</a>' : '';
$breadcrumb             .= $building_name_w_code ? '<i class="fas fa-chevron-right mx-4"></i><span class="d-inline-block">' . $building_name_w_code . '</span>' : '';

$alert_message = $building_fields['Alert Message'] ?? null;

$building_address = $building_fields['Building Address (override)'] ?? null;
$building_campus  = $building_fields['Campus'] ?? 'Vancouver'; // TODO - get real dynamic data if applicable

$building_hours_original = $building_fields['Hours'][0] ?? null;
$building_hours_override = $building_fields['Hours (override)'] ?? null;
$building_hours          = $building_hours_override ?? $building_hours_original;
$building_hours          = $building_hours ? array_map( 'trim', explode( ';', $building_hours ) ) : array();

$building_notes = isset( $building_fields['Building Notes'] ) ? nl2br( $building_fields['Building Notes'] ) : null; // TODO - the data returned by this column needs to be reformatted to include only the notes and nothing about hours

$building_image = $building_fields['Building Image'][0] ?? array();

$building_image_url    = $building_image['url'] ?? null;
$building_image_width  = $building_image['width'] ?? null;
$building_image_height = $building_image['height'] ?? null;
$building_image_alt    = $building_name ?? null;

$building_image_string  = isset( $building_image_url ) ? '<img src="' . $building_image_url . '"' : '';
$building_image_string .= isset( $building_image_width ) ? ' width="' . $building_image_width . '"' : '';
$building_image_string .= isset( $building_image_height ) ? ' height="' . $building_image_height . '"' : '';
$building_image_string .= isset( $building_image_alt ) ? ' alt="' . $building_image_alt . '"' : '';
$building_image_string .= isset( $building_image_url ) ? '>' : '';

$building_map = isset( $building_fields['Map Link'] ) ? $building_fields['Map Link'] : null;

$building_classrooms = isset( $args['building_classrooms'] ) && ! empty( $args['building_classrooms'] ) ? $args['building_classrooms'] : array();
?>

<section class="vpfo-spaces-page">
	<div class="container-fluid px-0">

		<section class="building-header mt-9">
			<?php if ( $breadcrumb && ! empty( $breadcrumb ) ) { ?>
				<div class="breadcrumb d-md-flex align-items-md-center px-0 mb-5 text-uppercase">
					<?php echo wp_kses_post( $breadcrumb ); ?>
				</div>
			<?php } ?>

			<div class="d-flex flex-column flex-lg-row justify-content-lg-between">
				<h1 class="text-uppercase">
					<?php
					if ( $building_name_w_code ) {
						echo wp_kses_post( $building_name_w_code );
					} elseif ( $building_name ) {
						echo wp_kses_post( $building_name );
					} else {
						esc_html_e( 'Learning Spaces', 'ubc-vpfo-spaces-pages' );
					}
					?>
				</h1>
				<a href="<?php echo wp_kses_post( $breadcrumb_find_a_space ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Return to Find a Space', 'ubc-vpfo-spaces-pages' ); ?></a>
			</div>

			<?php if ( $alert_message ) { ?>
				<div class="alert-message d-flex align-items-top align-items-lg-center">
					<i class="fa-solid fa-circle-info"></i>
					<span><?php echo wp_kses_post( $alert_message ); ?></span>
				</div>
			<?php } ?>
		</section>

		<section class="building-details">
			<div class="row">
				<div class="col-lg-6 pe-lg-5">
					<div class="building-info">
						<?php if ( $building_address ) { ?>
							<div class="building-address">
								<h2 class="text-uppercase"><?php esc_html_e( 'Address', 'ubc-vpfo-spaces-pages' ); ?></h2>
								<p><?php echo wp_kses_post( $building_address ); ?></p>
							</div>
						<?php } ?>

						<?php if ( $building_campus ) { ?>
							<div class="building-campus">
								<h2 class="text-uppercase"><?php esc_html_e( 'Campus', 'ubc-vpfo-spaces-pages' ); ?></h2>
								<p><?php echo wp_kses_post( $building_campus ); ?></p>
							</div>
						<?php } ?>

						<?php if ( ! empty( $building_hours ) ) { ?>
							<div class="building-hours">
								<h2 class="text-uppercase"><?php esc_html_e( 'Hours', 'ubc-vpfo-spaces-pages' ); ?></h2>
								<?php foreach ( $building_hours as $building_hours_set ) { ?>
									<p><?php echo wp_kses_post( $building_hours_set ); ?></p>
								<?php } ?>
							</div>
						<?php } ?>

						<?php if ( $building_notes ) { ?>
							<div class="building-notes">
								<h2 class="text-uppercase"><?php esc_html_e( 'Building Notes', 'ubc-vpfo-spaces-pages' ); ?></h2>
								<p><?php echo wp_kses_post( $building_notes ); ?></p>
							</div>
						<?php } ?>

						<?php // TODO - figure out real dynamic data and labelling for this whole section ?>
						<div class="building-resources">
							<h2 class="text-uppercase"><?php esc_html_e( 'Building Resources', 'ubc-vpfo-spaces-pages' ); ?> - Label TBD</h2>
							
							<div class="d-flex align-items-center building-amenities">
								<p class="mb-0"><?php esc_html_e( 'Inclusive Washrooms', 'ubc-vpfo-spaces-pages' ); ?></p>
								<i class="fa-solid fa-users ms-2"></i>
							</div>

							<div class="building-floor-plan">
								<a href="#" class="d-flex align-items-center">
									<p class="mb-0"><?php esc_html_e( 'Floor Plan', 'ubc-vpfo-spaces-pages' ); ?></p>
									<i class="fa-regular fa-file-pdf ms-2"></i>
								</a>
							</div>

							<div class="building-blue-phones">
								<a href="#" class="d-flex align-items-center">
									<p class="mb-0"><?php esc_html_e( 'Blue Phones Map', 'ubc-vpfo-spaces-pages' ); ?></p>
									<i class="fa-solid fa-location-dot ms-2"></i>
								</a>
							</div>

							<div class="building-accessibility-shuttle-map">
								<a href="#" class="d-flex align-items-center">
									<p class="mb-0"><?php esc_html_e( 'Accessibility Shuttle Map', 'ubc-vpfo-spaces-pages' ); ?></p>
									<i class="fa-solid fa-location-dot ms-2"></i>
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-6 ps-lg-5">
					<?php if ( $building_map ) { ?>
						<div class="building-map ratio ratio-4x3">
							<iframe src="<?php echo esc_url( $building_map ); ?>" title="Wayfinding Map"></iframe>
						</div>
					<?php } ?>

					<?php if ( $building_image_string && ! empty( $building_image_string ) ) { ?>
						<div class="building-image">
							<?php echo wp_kses_post( $building_image_string ); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</section>

		<?php if ( ! empty( $building_classrooms ) ) { ?>
			<section class="building-classrooms">
				<h2 class="text-uppercase"><?php esc_html_e( 'Classrooms &amp; Spaces', 'ubc-vpfo-spaces-pages' ); ?><?php echo ' — ' . wp_kses_post( $building_name ); ?></h2>

				<?php
				foreach ( $building_classrooms as $building_classroom ) {
					load_template( plugin_dir_path( __DIR__ ) . 'templates/partials/classroom-card.php', false, array( 'building_classroom' => $building_classroom ) );
				}
				?>
			</section>
			<?php
		}
		?>
		
	</div>
</section>

<?php get_footer(); ?>