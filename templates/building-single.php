<?php
// Define variables needed before we call the header to support redirect logic
$building        = $args['building'];
$building_fields = json_decode( wp_json_encode( $building->fields ), true );

$building_classrooms = isset( $args['building_classrooms'] ) && ! empty( $args['building_classrooms'] ) ? $args['building_classrooms'] : (object) array();

// Get the total number of classrooms before slicing for pagination
$total_classrooms = count( $building_classrooms );

// Define the number of items per page
$classrooms_per_page = 12;

// Calculate the total number of pages and cast to integer
$max_pages = (int) ceil( $total_classrooms / $classrooms_per_page );

// Get the current page from the URL parameter and ensure it's within bounds
$classrooms_page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

// Prep for the redirect logic
// Strip the fragment (anything after #) from the URL
$current_url = get_bloginfo( 'url' ) . $_SERVER['REQUEST_URI'];
$parsed_url  = wp_parse_url( $current_url );

// Rebuild the URL without the fragment
$base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';

// Calculate the last page URL and apply the page parameter
$last_page_url = add_query_arg( 'page', $max_pages, $base_url . $query );

// Perform the redirect if the current page is greater than the max
if ( $classrooms_page > $max_pages ) {
	wp_safe_redirect( $last_page_url );
	exit;
}

get_header();

// set up the rest of our variables
$building_name_original = $building_fields['Building Name'][0] ?? null;
$building_name_override = $building_fields['Building Name (override)'] ?? null;
$building_name          = $building_name_override ?? $building_name_original;
$building_code          = $building_fields['Building Code'][0] ?? null;
$building_name_w_code   = $building_name && $building_code ? $building_name . ' - ' . $building_code : $building_name;

$breadcrumb_home         = get_bloginfo( 'url' );
$breadcrumb_find_a_space = get_page_by_path( 'find-a-space' ) !== null ? get_permalink( get_page_by_path( 'find-a-space' ) ) : null;
$breadcrumb              = '<a href="' . $breadcrumb_home . '" class="d-inline-block" title="' . get_bloginfo( 'name' ) . '" rel="bookmark">' . get_bloginfo( 'name' ) . '</a>';
$breadcrumb             .= $breadcrumb_find_a_space ? '<i class="fas fa-chevron-right mx-4"></i><a href="' . $breadcrumb_find_a_space . '" class="d-inline-block">' . __( 'Find a Space', 'ubc-vpfo-spaces-pages' ) . '</a>' : '';
$breadcrumb             .= $building_name_w_code ? '<i class="fas fa-chevron-right mx-4"></i><span class="d-inline-block current-page">' . $building_name_w_code . '</span>' : '';

$alert_message = $building_fields['Alert Message'] ?? null;

$building_address = $building_fields['Building Address (override)'] ?? null;

$building_campus_code = $building_fields['Campus Code'][0] ?? null;
if ( $building_campus_code === 'UBCV' ) {
	$building_campus = 'Vancouver';
} elseif ( $building_campus_code === 'UBCO' ) {
	$building_campus = 'Okanagan';
}
else {
	$building_campus = null;
}

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
?>

<section class="vpfo-spaces-page">
	<div class="container-lg px-xl-0">

		<section class="building-header mt-9">
			<?php if ( $breadcrumb && ! empty( $breadcrumb ) ) { ?>
				<div class="breadcrumb d-flex flex-wrap align-items-center px-0 mb-9 text-uppercase">
					<?php echo wp_kses_post( $breadcrumb ); ?>
				</div>
			<?php } ?>

			<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-13">
				<h1 class="text-uppercase fw-bold mb-9 mb-md-0">
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
				<a href="<?php echo wp_kses_post( $breadcrumb_find_a_space ); ?>" class="btn btn-secondary btn-border-thick me-auto me-md-0 ms-md-auto  d-flex align-items-center"><i class="fas fa-chevron-left me-3"></i><?php esc_html_e( 'Return to Find a Space', 'ubc-vpfo-spaces-pages' ); ?></a>
			</div>

			<?php if ( $alert_message ) { ?>
				<div class="alert-message d-flex flex-column flex-sm-row align-items-sm-top p-5 mb-5">
					<i class="fa-solid fa-circle-info pt-1"></i>
					<span><?php echo wp_kses_post( $alert_message ); ?></span>
				</div>
			<?php } ?>
		</section>

		<section class="building-details">
			<div class="row">
				<div class="col-lg-6 pe-lg-5 order-2 order-lg-1">
					<div class="building-info px-5 py-7">
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
							<h2 class="text-uppercase"><?php esc_html_e( 'Resources', 'ubc-vpfo-spaces-pages' ); ?></h2>
							
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

							<div class="building-aed-map">
								<a href="#" class="d-flex align-items-center">
									<p class="mb-0"><?php esc_html_e( 'AED Shuttle Map', 'ubc-vpfo-spaces-pages' ); ?></p>
									<i class="fa-solid fa-location-dot ms-2"></i>
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-6 ps-lg-5 order-1 order-lg-2">
					<?php if ( $building_map || ( $building_image_string && ! empty( $building_image_string ) ) ) { ?>
						<div class="row">
							<?php if ( $building_map ) { ?>
								<div class="col-md-6 col-lg-12">
									<div class="building-map ratio mb-5 mb-md-0 mb-lg-5">
										<iframe src="<?php echo esc_url( $building_map ); ?>" title="Wayfinding Map"></iframe>
									</div>
								</div>
							<?php } ?>

							<?php if ( $building_image_string && ! empty( $building_image_string ) ) { ?>
								<div class="col-md-6 col-lg-12">
									<div class="building-image">
										<?php echo wp_kses_post( $building_image_string ); ?>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</section>

		<?php
		if ( ! empty( $building_classrooms ) ) {
			// Get the sorted and paginated classrooms - see includes/helpers.php
			$paginated_classrooms = get_sorted_and_paginated_classrooms( $building_classrooms, $classrooms_page );
			?>

			<section class="building-classrooms mt-9">
				<h2 class="text-uppercase"><?php esc_html_e( 'Classrooms &amp; Spaces', 'ubc-vpfo-spaces-pages' ); ?><?php echo ' — ' . wp_kses_post( $building_name ); ?></h2>

				<div class="classroom-list" id="classroom-list">
					<?php
					foreach ( $paginated_classrooms as $classroom ) {
						load_template( plugin_dir_path( __DIR__ ) . 'templates/partials/classroom-card.php', false, array( 'classroom' => $classroom['fields'] ) );
					}
					?>
				</div>

				<?php if ( count( $building_classrooms ) >= 12 ) { ?>

					<div class="classroom-list-nav d-flex mt-9">
						<!-- Prev Page Link -->
						<a href="
						<?php
						// If $classrooms_page is 2 or less, remove the 'page' query arg and append '#classroom-list'
						if ( $classrooms_page <= 2 ) {
							// Remove 'page' query arg and append '#classroom-list'
							echo esc_url( remove_query_arg( 'page' ) . '#classroom-list' );
						} else {
							// Otherwise, link to the previous page with '#classroom-list'
							echo esc_url( add_query_arg( 'page', $classrooms_page - 1 ) . '#classroom-list' );
						}
						?>
						" class="btn btn-primary me-3" 
						<?php
						echo $classrooms_page <= 1 ? 'disabled' : '';
						?>
						>
							<i class="fas fa-chevron-left me-2"></i>
						<?php
						esc_html_e( 'Prev Page', 'ubc-vpfo-spaces-pages' );
						?>
						</a>

						<!-- Next Page Link -->
						<a href="
						<?php
						// If $classrooms_page is the last page, keep the query arg for the last page with '#classroom-list'
						if ( $classrooms_page === $max_pages ) {
							echo esc_url( add_query_arg( 'page', $max_pages ) . '#classroom-list' );
						} else {
							// Otherwise, link to the next page with '#classroom-list'
							echo esc_url( add_query_arg( 'page', $classrooms_page + 1 ) . '#classroom-list' );
						}
						?>
						" class="btn btn-primary" 
						<?php
						echo count( $paginated_classrooms ) < 12 || $classrooms_page === $max_pages ? 'disabled' : '';
						?>
						>
						<?php
						esc_html_e( 'Next Page', 'ubc-vpfo-spaces-pages' );
						?>
							<i class="fas fa-chevron-right ms-2"></i>
						</a>
					</div>
				<?php } ?>

			</section>
			<?php
		}
		?>

		<div class="pattern-slice mt-9"></div>
		
	</div>
</section>

<?php get_footer(); ?>