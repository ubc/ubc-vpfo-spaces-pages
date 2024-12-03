<?php get_header();

$classroom        = $args['classroom'];
$classroom_fields = json_decode( wp_json_encode( $classroom->fields ), true );

$classroom_name = $classroom_fields['Name'] ?? null;

$classroom_building_name_original = $classroom_fields['Buildings - Building Name'][0] ?? null;
$classroom_building_name_override = $classroom_fields['Buildings - Building Name (override)'][0] ?? null;
$classroom_building_name          = $classroom_building_name_override ?? $classroom_building_name_original;

$classroom_building_code  = $classroom_fields['Building Code'] ?? null;
$classroom_building_title = $classroom_building_name ?? '';
$classroom_building_slug  = $classroom_fields['Building Slug'][0] ?? null;
$classroom_building_url   = $classroom_building_slug ? get_bloginfo( 'url' ) . '/buildings/' . $classroom_building_slug : null;

// For use in the Copy to Clipboard button.
$classroom_slug = $classroom_fields['Slug'] ?? null;
$classroom_url  = $classroom_slug ? get_bloginfo( 'url' ) . '/classrooms/' . $classroom_slug : null;

$breadcrumb_home         = get_bloginfo( 'url' );
$breadcrumb_find_a_space = get_page_by_path( 'find-a-space' ) !== null ? get_permalink( get_page_by_path( 'find-a-space' ) ) : null;
$breadcrumb_building     = $classroom_building_url ? '<a href="' . $classroom_building_url . '" class="d-inline-block vpfo-building-link" title="' . $classroom_building_title . '" rel="bookmark">' . $classroom_building_title . ' - ' . $classroom_building_code . '</a>' : null;
$breadcrumb              = '<a href="' . $breadcrumb_home . '" class="d-inline-block" title="' . get_bloginfo( 'name' ) . '" rel="bookmark">' . get_bloginfo( 'name' ) . '</a>';
$breadcrumb             .= $breadcrumb_find_a_space ? '<i class="fas fa-chevron-right mx-4"></i><a href="' . $breadcrumb_find_a_space . '" class="d-inline-block vpfo-return-to-lsf">' . __( 'Find a Space', 'ubc-vpfo-spaces-pages' ) . '</a>' : '';
$breadcrumb             .= $breadcrumb_building ? '<i class="fas fa-chevron-right mx-4"></i>' . $breadcrumb_building : '';
$breadcrumb             .= $classroom_name ? '<i class="fas fa-chevron-right mx-4"></i><span class="d-inline-block current-page">' . $classroom_name . '</span>' : '';

$classroom_is_informal = $classroom_fields['Is Informal Space'] ?? false;

$classroom_workday_room_code_original = $classroom_fields['Workday Room Code'] ?? null;
$classroom_workday_room_code_override = $classroom_fields['Workday Room Code (override)'] ?? null;
$classroom_workday_room_code          = $classroom_workday_room_code_override ?? $classroom_workday_room_code_original;

$classroom_alert_message = $classroom_fields['Alert Message'] ?? null;
if ( trim( $classroom_alert_message ) === '' ) {
	$classroom_alert_message = null;
}

$classroom_image_gallery = $classroom_fields['Image Gallery'] ?? array();
$classroom_layout_image  = $classroom_fields['Classroom Layout'][0] ?? array();

$classroom_capacity                        = $classroom_fields['Capacity'] ?? null;
$classroom_capacity_update                 = $classroom_fields['Capacity Update'] ?? null;
$classroom_capacity_effective_on           = $classroom_fields['Effective On'] ?? null;
$classroom_capacity_effective_on_formatted = gmdate( 'F j, Y', strtotime( $classroom_capacity_effective_on ) );

$classroom_hours_override = $classroom_fields['Hours (override)'] ?? null;

$classroom_overview = $classroom_fields['Space Overview'] ?? null;
if ( trim( $classroom_overview ) === '' ) {
	$classroom_overview = null;
}

$classroom_shared_av_guide = $classroom_fields['Shared AV Guide'] ?? null;
$classroom_360_view        = $classroom_fields['360 View'] ?? null;
$classroom_av_guide        = $classroom_fields['AV Guide'][0]['url'] ?? null;
$classroom_outlets_layout  = $classroom_fields['Electrical Outlets Layout'][0]['url'] ?? null;
$classroom_av_helpdesk     = 'tel:6048227956';

$classroom_layout_type = $classroom_fields['Formatted_Room_Layout_Type'] ?? null;

$classroom_furniture_source = $classroom_fields['Formatted_Furniture'] ?? null;
$classroom_furniture_source = str_replace( '"', '', $classroom_furniture_source );
$classroom_furniture        = $classroom_furniture_source ? explode( ', ', $classroom_furniture_source ) : array();

$classroom_accessibility_source = $classroom_fields['Formatted_Accessibility'] ?? null;
$classroom_accessibility_source = str_replace( '"', '', $classroom_accessibility_source );
$classroom_accessibility        = $classroom_accessibility_source ? explode( ', ', $classroom_accessibility_source ) : array();

$classroom_accessibility_content = $classroom_fields['Accessibility Notes'] ?? null;
if ( trim( $classroom_accessibility_content ) === '' ) {
	$classroom_accessibility_content = null;
}

$classroom_is_features_source = $classroom_fields['Formatted_IS_Amenities'] ?? null;
$classroom_is_features_source = str_replace( '"', '', $classroom_is_features_source );
$classroom_is_features        = $classroom_is_features_source ? explode( ', ', $classroom_is_features_source ) : array();

$classroom_features_source = $classroom_fields['Formatted_Amenities_Other_Room_Features'] ?? null;
$classroom_features_source = str_replace( '"', '', $classroom_features_source );
$classroom_features        = $classroom_features_source ? explode( ', ', $classroom_features_source ) : array();
$classroom_features        = array_merge( $classroom_features, $classroom_is_features );
$classroom_features        = array_filter(
	$classroom_features,
	function ( $item ) {
		return strpos( $item, 'Any' ) === false;
	}
);

$classroom_presentation_displays_source = $classroom_fields['Formatted_Amenities_Presentation_Displays'] ?? null;
$classroom_presentation_displays_source = str_replace( '"', '', $classroom_presentation_displays_source );
$classroom_presentation_displays        = $classroom_presentation_displays_source ? explode( ', ', $classroom_presentation_displays_source ) : array();
$classroom_presentation_displays        = array_filter(
	$classroom_presentation_displays,
	function ( $item ) {
		return strpos( $item, 'Any' ) === false;
	}
);

$classroom_presentation_sources_source = $classroom_fields['Formatted_Amenities_Presentation_Sources'] ?? null;
$classroom_presentation_sources_source = str_replace( '"', '', $classroom_presentation_sources_source );
$classroom_presentation_sources        = $classroom_presentation_sources_source ? explode( ', ', $classroom_presentation_sources_source ) : array();
$classroom_presentation_sources        = array_filter(
	$classroom_presentation_sources,
	function ( $item ) {
		return strpos( $item, 'Any' ) === false;
	}
);

$classroom_audio_source = $classroom_fields['Formatted_Amenities_Audio'] ?? null;
$classroom_audio_source = str_replace( '"', '', $classroom_audio_source );
$classroom_audio        = $classroom_audio_source ? explode( ', ', $classroom_audio_source ) : array();
$classroom_audio        = array_filter(
	$classroom_audio,
	function ( $item ) {
		return strpos( $item, 'Any' ) === false;
	}
);

$classroom_other_av_source = $classroom_fields['Formatted_Amenities_Other_AV_Features'] ?? null;
$classroom_other_av_source = str_replace( '"', '', $classroom_other_av_source );
$classroom_other_av        = $classroom_other_av_source ? explode( ', ', $classroom_other_av_source ) : array();

$classroom_building_map  = $classroom_building_code ? 'https://maps.ubc.ca/?code=' . $classroom_building_code : null;
$classroom_map_col_class = $classroom_is_informal ? 'col-lg-12' : 'col-lg-8  ps-lg-5';
?>

<section class="vpfo-spaces-page">
	<div class="container-lg px-lg-0">

		<section class="classroom-header mt-9">
			<?php
			if ( $breadcrumb && ! empty( $breadcrumb ) ) {
				?>
				<div class="breadcrumb d-flex flex-wrap align-items-center px-0 mb-9 text-uppercase">
					<?php echo wp_kses_post( $breadcrumb ); ?>
				</div>
				<?php
			}
			?>

			<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-start mb-9">
				<div class="classroom-title">
					<h1 class="text-uppercase fw-bold mb-0">
						<?php
						if ( $classroom_name ) {
							echo wp_kses_post( $classroom_name );
						} else {
							esc_html_e( 'Learning Spaces', 'ubc-vpfo-spaces-pages' );
						}
						?>
					</h1>
					
					<?php
					if ( $classroom_building_title ) {
						?>
						<a href="<?php echo esc_url( $classroom_building_url ); ?>" class="d-inline-block vpfo-building-link building-title text-uppercase fw-bold mt-3" title="<?php echo wp_kses_post( $classroom_building_title ); ?>" rel="bookmark"><?php echo wp_kses_post( $classroom_building_title ); ?></a>
						<?php
					}
					?>

					<?php
					if ( $classroom_workday_room_code && ! $classroom_is_informal ) {
						?>
						<div class="workday-room-code text-uppercase mt-3"><?php echo wp_kses_post( $classroom_workday_room_code ); ?></div>
						<?php
					}
					?>

					<?php
					if ( $classroom_url ) {
						?>
						<div class="clippy text-uppercase mt-3" data-clipboard-text="<?php echo esc_url( $classroom_url ); ?>">
							<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.28613 1.78405C8.71426 1.21217 7.78613 1.21217 7.21426 1.78405L2.90176 6.09654C1.91504 7.08326 1.91504 8.6817 2.90176 9.66842C3.88848 10.6551 5.48691 10.6551 6.47363 9.66842L10.0361 6.10592C10.2916 5.85045 10.7088 5.85045 10.9643 6.10592C11.2197 6.36139 11.2197 6.77858 10.9643 7.03404L7.40176 10.5965C5.90176 12.0965 3.47363 12.0965 1.97363 10.5965C0.473633 9.09654 0.473633 6.66842 1.97363 5.16842L6.28613 0.85592C7.37129 -0.229236 9.1291 -0.229236 10.2143 0.85592C11.2994 1.94108 11.2994 3.69889 10.2143 4.78405L6.08926 8.90904C5.41895 9.57936 4.33145 9.57936 3.66113 8.90904C2.99082 8.23873 2.99082 7.15123 3.66113 6.48092L7.03613 3.10592C7.2916 2.85045 7.70879 2.85045 7.96426 3.10592C8.21973 3.36139 8.21973 3.77858 7.96426 4.03405L4.58926 7.40904C4.43223 7.56608 4.43223 7.82389 4.58926 7.98092C4.74629 8.13795 5.0041 8.13795 5.16113 7.98092L9.28613 3.85592C9.85801 3.28405 9.85801 2.35592 9.28613 1.78405Z" fill="#0055B7"/>
							</svg>
							Copy permalink
						</div>
						<?php
					}
					?>
					
				</div>
				<a href="<?php echo wp_kses_post( $breadcrumb_find_a_space ); ?>" class="btn btn-secondary btn-border-thick mt-9 mt-md-0 me-auto me-md-0 ms-md-auto d-flex align-items-center vpfo-return-to-lsf"><i class="fas fa-chevron-left me-3"></i><?php esc_html_e( 'Return to Find a Space', 'ubc-vpfo-spaces-pages' ); ?></a>
			</div>

			<?php
			if ( $classroom_alert_message ) {
				?>
				<div class="alert-message d-flex flex-column flex-sm-row align-items-md-top p-5 mb-13 mb-md-9">
					<i class="fa-solid fa-circle-info pt-1"></i>
					<span><?php echo wp_kses_post( $classroom_alert_message ); ?></span>
				</div>
				<?php
			}
			?>
		</section>

		<?php
		if ( ! empty( $classroom_image_gallery ) ) {
			?>
			<section class="classroom-image-gallery">
				<?php
				if ( count( $classroom_image_gallery ) === 1 ) {
					?>
					<div class="classroom-image">
						<?php
						$image_thumbnails    = $classroom_image_gallery[0]['thumbnails'] ?? array();
						$image_full          = $image_thumbnails['full'] ?? array();
						$image_full_url      = $image_full['url'] ?? null;
						$image_full_width    = $image_full['width'] ?? null;
						$image_full_height   = $image_full['height'] ?? null;
						$image_full_alt      = $classroom_name . ' - Image Gallery 1';
						$image_full_element  = '';
						$image_full_element .= $image_full_url ? '<img src="' . $image_full_url . '"' : '';
						$image_full_element .= $image_full_width ? ' width="' . $image_full_width . '"' : '';
						$image_full_element .= $image_full_height ? ' height="' . $image_full_height . '"' : '';
						$image_full_element .= $image_full_alt ? ' alt="' . $image_full_alt . '"' : '';
						$image_full_element .= $image_full_url ? '>' : '';

						if ( $image_full_element ) {
							echo wp_kses_post( $image_full_element );
						}
						?>
					</div>
					<?php
				} else {
					?>
					<div class="glider-contain position-relative">
						<div class="glider">
							<?php
							$image_full_counter = 0;
							foreach ( $classroom_image_gallery as $image ) {
								++$image_full_counter;
								$image_thumbnails    = $image['thumbnails'] ?? array();
								$image_full          = $image_thumbnails['full'] ?? array();
								$image_full_url      = $image_full['url'] ?? null;
								$image_full_width    = $image_full['width'] ?? null;
								$image_full_height   = $image_full['height'] ?? null;
								$image_full_alt      = $classroom_name . ' - Image Gallery ' . $image_full_counter;
								$image_full_element  = '';
								$image_full_element .= $image_full_url ? '<img src="' . $image_full_url . '"' : '';
								$image_full_element .= $image_full_width ? ' width="' . $image_full_width . '"' : '';
								$image_full_element .= $image_full_height ? ' height="' . $image_full_height . '"' : '';
								$image_full_element .= $image_full_alt ? ' alt="' . $image_full_alt . '"' : '';
								$image_full_element .= $image_full_url ? '>' : '';

								if ( $image_full_element ) {
									?>
									<div class="glider-slide">
										<?php echo wp_kses_post( $image_full_element ); ?>
									</div>
									<?php
								}
							}
							?>
						</div>

						<div class="glider-thumbnails d-none d-md-flex justify-content-md-center align-items-md-center position-absolute p-1 p-md-3 p-lg-5 w-100">
							<?php
							$image_thumb_count   = count( $classroom_image_gallery );
							$image_thumb_counter = 0;
							foreach ( $classroom_image_gallery as $image ) {
								++$image_thumb_counter;
								$image_thumb_thumbnails = $image['thumbnails'] ?? array();
								$image_thumb            = $image_thumb_thumbnails['large'] ?? array();
								$image_thumb_url        = $image_thumb['url'] ?? null;
								$image_thumb_width      = $image_thumb['width'] ?? null;
								$image_thumb_height     = $image_thumb['height'] ?? null;
								$image_thumb_alt        = $classroom_name . ' - Image Gallery ' . $image_thumb_counter;
								$image_thumb_element    = '';
								$image_thumb_element   .= $image_thumb_url ? '<img src="' . $image_thumb_url . '" data-index"' . $image_thumb_counter . '"' : '';
								$image_thumb_element   .= $image_thumb_width ? ' width="' . $image_thumb_width . '"' : '';
								$image_thumb_element   .= $image_thumb_height ? ' height="' . $image_thumb_height . '"' : '';
								$image_thumb_element   .= $image_thumb_alt ? ' alt="' . $image_thumb_alt . '"' : '';
								$image_thumb_element   .= $image_thumb_url ? '>' : '';

								if ( $image_full_element ) {
									?>
									<div class="glider-thumbnail thumbs-<?php echo absint( $image_thumb_count ); ?>" tabindex="0" role="button" aria-label="Image <?php echo absint( $image_thumb_counter ); ?>">
										<?php
										echo wp_kses_post( $image_thumb_element );
										?>
									</div>
									<?php
								}
							}
							?>
						</div>

						<div class="glider-nav">
							<button aria-label="Previous" class="glider-prev"><i class="fas fa-chevron-left"></i></button>
							<button aria-label="Next" class="glider-next"><i class="fas fa-chevron-right"></i></button>
						</div>

						<div class="glider-dots d-md-none position-absolute pb-5 w-100"></div>
					</div>
					<?php
				}
				?>
			</section>
			<?php
		}
		?>

		<section class="classroom-details mt-13 mt-md-9">

			<div class="row">
				<div class="col-lg-6 order-2 order-lg-1 pe-lg-5">
					<div class="classroom-info mt-9 mt-lg-0">
						<?php
						if ( ! empty( $classroom_layout_image ) ) {
							$classroom_layout_url      = $classroom_layout_image['url'] ?? null;
							$classroom_layout_width    = $classroom_layout_image['width'] ?? null;
							$classroom_layout_height   = $classroom_layout_image['height'] ?? null;
							$classroom_layout_alt      = $classroom_name . ' - Layout Image';
							$classroom_layout_element  = '';
							$classroom_layout_element .= $classroom_layout_url ? '<img src="' . $classroom_layout_url . '"' : '';
							$classroom_layout_element .= $classroom_layout_width ? ' width="' . $classroom_layout_width . '"' : '';
							$classroom_layout_element .= $classroom_layout_height ? ' height="' . $classroom_layout_height . '"' : '';
							$classroom_layout_element .= $classroom_layout_alt ? ' alt="' . $classroom_layout_alt . '"' : '';
							$classroom_layout_element .= $classroom_layout_url ? '>' : '';

							if ( $classroom_layout_element ) {
								?>
								<div class="classroom-layout d-none d-lg-block">
									<a href="#classroom-layout-modal" data-overlay-trigger>
										<?php echo wp_kses_post( $classroom_layout_element ); ?>
									</a>
								</div>
								<div class="classroom-layout d-lg-none">
									<?php echo wp_kses_post( $classroom_layout_element ); ?>
								</div>
								<div class="d-none">
									<!-- Each overlay needs an id -->
									<div id="classroom-layout-modal">
										<?php echo wp_kses_post( $classroom_layout_element ); ?>
									</div>
								</div>
								<?php
							}
						}
						?>

						<div class="p-5">
							<?php
							if ( $classroom_capacity ) {
								?>
								<div class="classroom-capacity">
									<h2 class="text-uppercase"><?php esc_html_e( 'Capacity', 'ubc-vpfo-spaces-pages' ); ?></h2>
									<p>
										<?php
										echo wp_kses_post( $classroom_capacity );

										if ( $classroom_capacity_update && $classroom_capacity_effective_on_formatted ) {
											echo wp_kses_post( '<br>' . $classroom_capacity_update . ' as of ' . $classroom_capacity_effective_on_formatted );
										}
										?>
									</p>
								</div>
								<?php
							}

							if ( $classroom_hours_override ) {
								?>
								<div class="classroom-hours">
									<h2 class="text-uppercase"><?php esc_html_e( 'Classroom Hours', 'ubc-vpfo-spaces-pages' ); ?></h2>
									<p><?php echo wp_kses_post( $classroom_hours_override ); ?></p>
								</div>
								<?php
							}

							if ( $classroom_overview ) {
								?>
								<div class="classroom-overview">
									<h2 class="text-uppercase"><?php esc_html_e( 'Space Overview', 'ubc-vpfo-spaces-pages' ); ?></h2>
									<p><?php echo wp_kses_post( $classroom_overview ); ?></p>
								</div>
								<?php
							}

							if ( ( $classroom_360_view || $classroom_shared_av_guide || $classroom_av_guide || $classroom_outlets_layout || $classroom_av_helpdesk ) && ! $classroom_is_informal ) {
								?>
								<div class="classroom-resources">
									<h2 class="text-uppercase"><?php esc_html_e( 'Resources', 'ubc-vpfo-spaces-pages' ); ?></h2>

									<div class="classroom-resources-links d-flex flex-column flex-sm-row flex-lg-wrap w-100">
										<?php
										if ( $classroom_360_view ) {
											?>
											<div class="btn-wrapper">
												<a href="<?php echo esc_url( $classroom_360_view ); ?>" class="btn btn-secondary d-block" target="_blank">
													<?php esc_html_e( '360 View', 'ubc-vpfo-spaces-pages' ); ?>
													<i class="fas fa-eye ms-3"></i>
												</a>
											</div>
											<?php
										}

										if ( $classroom_av_guide ) {
											?>
											<div class="btn-wrapper">
												<a href="<?php echo esc_url( $classroom_av_guide ); ?>" class="btn btn-secondary d-block" target="_blank" rel="external">
													<?php esc_html_e( 'AV Guide', 'ubc-vpfo-spaces-pages' ); ?>
													<i class="far fa-file-pdf ms-3"></i>
												</a>
											</div>
											<?php
										}

										if ( $classroom_shared_av_guide ) {
											?>
											<div class="btn-wrapper">
												<a href="<?php echo esc_url( $classroom_shared_av_guide ); ?>" class="btn btn-secondary d-block" target="_blank" rel="external">
													<?php esc_html_e( 'AV Guide', 'ubc-vpfo-spaces-pages' ); ?>
													<i class="far fa-file-pdf ms-3"></i>
												</a>
											</div>
											<?php
										}

										if ( $classroom_outlets_layout ) {
											?>
											<div class="btn-wrapper">
												<a href="<?php echo esc_url( $classroom_outlets_layout ); ?>" class="btn btn-secondary d-block" target="_blank" rel="external">
													<?php esc_html_e( 'Electrical Outlets Layout', 'ubc-vpfo-spaces-pages' ); ?>
													<i class="fas fa-plug ms-3"></i>
												</a>
											</div>
											<?php
										}

										if ( $classroom_av_helpdesk ) {
											?>
											<div class="btn-wrapper">
												<a href="<?php echo esc_url( $classroom_av_helpdesk ); ?>" class="btn btn-secondary d-block">
													<?php esc_html_e( 'AV Helpdesk', 'ubc-vpfo-spaces-pages' ); ?>
													<i class="fas fa-phone ms-3"></i>
												</a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>

				<div class="col-lg-6 order-1 order-lg-2 ps-lg-5">
					<?php
					if ( $classroom_layout_type || $classroom_furniture ) {
						?>
						<div class="accordion">
							<div class="ac">
								<h2 class="ac-header">
									<button class="ac-trigger" type="button">
										<?php esc_html_e( 'Style - Furniture &amp; Layout', 'ubc-vpfo-spaces-pages' ); ?>
									</button>
								</h2>
								<div class="ac-panel">
									<div class="ac-panel-inner">
										<?php

										if ( ! empty( $classroom_furniture ) ) {
											?>
											<div class="classroom-furniture">
												<h3><?php esc_html_e( 'Furniture', 'ubc-vpfo-spaces-pages' ); ?></h3>
												<ul>
													<?php
													foreach ( $classroom_furniture as $furniture_item ) {
														?>
														<li><?php echo wp_kses_post( $furniture_item ); ?></li>
														<?php
													}
													?>
												</ul>
											</div>
											<?php
										}

										if ( $classroom_layout_type ) {
											?>
											<div class="classroom-layout-type">
												<h3><?php esc_html_e( 'Classroom Layout', 'ubc-vpfo-spaces-pages' ); ?></h3>
												<ul>
													<li><?php echo wp_kses_post( $classroom_layout_type ); ?></li>
												</ul>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<?php
					}

					if ( ! empty( $classroom_accessibility ) || $classroom_accessibility_content || ! empty( $classroom_features ) || ! empty( $classroom_presentation_displays ) || ! empty( $classroom_presentation_sources ) || ! empty( $classroom_audio ) || ! empty( $classroom_other_av ) ) {
						?>
						<div class="accordion">
							<div class="ac">
								<h2 class="ac-header">
									<button class="ac-trigger" type="button">
										<?php esc_html_e( 'Amenities - Accessibility, Features &amp; AV', 'ubc-vpfo-spaces-pages' ); ?>
									</button>
								</h2>
								<div class="ac-panel">
									<div class="ac-panel-inner">
										<div class="classroom-accessibility">
											<h3><?php esc_html_e( 'Accessibility', 'ubc-vpfo-spaces-pages' ); ?></h3>
											<?php
											if ( ! empty( $classroom_accessibility ) ) {
												?>
												<ul>
													<?php foreach ( $classroom_accessibility as $accessibility_item ) { ?>
														<li>
															<?php
															echo wp_kses_post( $accessibility_item );
															?>
														</li>
														<?php
													}
													?>
												</ul>
												<?php
											}

											if ( $classroom_accessibility_content ) {
												?>
												<p><?php echo wp_kses_post( $classroom_accessibility_content ); ?></p>
												<?php
											}
											?>
											
											<p class="accessibility-cta">
												<a href="https://students.ubc.ca/about-student-services/centre-for-accessibility" target="_blank"><?php esc_html_e( 'Contact the Centre for Accessibility', 'ubc-vpfo-spaces-pages' ); ?></a>
											</p>
										</div>

										<?php
										if ( ! empty( $classroom_features ) ) {
											?>
											<div class="classroom-features">
												<h3><?php esc_html_e( 'Features', 'ubc-vpfo-spaces-pages' ); ?></h3>
												<ul>
													<?php
													foreach ( $classroom_features as $feature_item ) {
														?>
														<li>
															<?php
															echo wp_kses_post( $feature_item );
															?>
														</li>
														<?php
													}
													?>
												</ul>
											</div>
											<?php
										}

										if ( ! empty( $classroom_presentation_displays ) || ! empty( $classroom_presentation_sources ) || ! empty( $classroom_audio ) || ! empty( $classroom_other_av ) ) {
											?>
											<div class="classroom-av">
												<h3><?php esc_html_e( 'Audio Visual', 'ubc-vpfo-spaces-pages' ); ?></h3>

												<div class="classroom-av-inner d-flex flex-column flex-sm-row flex-sm-wrap">
													<?php
													if ( ! empty( $classroom_presentation_displays ) ) {
														?>
														<div class="classroom-presentation-displays">
															<h4 class="text-uppercase"><?php esc_html_e( 'Presentation Displays', 'ubc-vpfo-spaces-pages' ); ?></h4>
															<ul>
																<?php
																foreach ( $classroom_presentation_displays as $presentation_display_item ) {
																	?>
																	<li>
																		<?php
																		echo wp_kses_post( $presentation_display_item );
																		?>
																	</li>
																	<?php
																}
																?>
															</ul>
														</div>
														<?php
													}

													if ( ! empty( $classroom_presentation_sources ) ) {
														?>
														<div class="classroom-presentation-sources">
															<h4 class="text-uppercase"><?php esc_html_e( 'Presentation Sources', 'ubc-vpfo-spaces-pages' ); ?></h4>
															<ul>
																<?php
																foreach ( $classroom_presentation_sources as $presentation_source_item ) {
																	?>
																	<li>
																		<?php
																		echo wp_kses_post( $presentation_source_item );
																		?>
																	</li>
																	<?php
																}
																?>
															</ul>
														</div>
														<?php
													}

													if ( ! empty( $classroom_audio ) ) {
														?>
														<div class="classroom-audio">
															<h4 class="text-uppercase"><?php esc_html_e( 'Audio', 'ubc-vpfo-spaces-pages' ); ?></h4>
															<ul>
																<?php
																foreach ( $classroom_audio as $audio_item ) {
																	?>
																	<li>
																		<?php
																		echo wp_kses_post( $audio_item );
																		?>
																	</li>
																	<?php
																}
																?>
															</ul>
														</div>
														<?php
													}

													if ( ! empty( $classroom_other_av ) ) {
														?>
														<div class="classroom-other-av">
															<h4 class="text-uppercase"><?php esc_html_e( 'Other AV Features', 'ubc-vpfo-spaces-pages' ); ?></h4>
															<ul>
																<?php
																foreach ( $classroom_other_av as $other_av_item ) {
																	?>
																	<li>
																		<?php
																		echo wp_kses_post( $other_av_item );
																		?>
																	</li>
																	<?php
																}
																?>
															</ul>
														</div>
														<?php
													}
													?>
												</div>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</section>

		<section class="classroom-book-space mt-9 mt-lg-17
		<?php
		if ( $classroom_is_informal ) {
			echo esc_html( ' no-shadow' );
		}
		?>
		">
			<div class="row">
				<?php
				if ( ! $classroom_is_informal ) {
					?>
					<div class="col-lg-4 d-lg-flex align-items-lg-center pe-lg-5">
						<div class="book-space-content p-5">
							<h2 class="mb-4 fw-bold"><?php esc_html_e( 'Book a space', 'ubc-vpfo-spaces-pages' ); ?></h2>
							<p class="mt-0"><?php esc_html_e( 'To find out how to book this room, visit the Room Booking Request page.', 'ubc-vpfo-spaces-pages' ); ?></p>
							<div class="d-flex flex-wrap align-items-center button-container">
								<a href="<?php echo esc_url( 'https://facultystaff.students.ubc.ca/enrolment-services/scheduling-records-systems-management/scheduling-services/room-booking-requests-general-teaching-space' ); ?>" class="btn btn-primary" target="_blank" title="UBC Room Booking Requests">
									<span><?php esc_html_e( 'Book Space', 'ubc-vpfo-spaces-pages' ); ?></span>
									<i class="fas fa-arrow-up-right-from-square ms-3"></i>
								</a>

								<a href="<?php echo esc_url( $classroom_building_map ); ?>" class="btn btn-secondary" target="_blank" title="Wayfinding at UBC Map for <?php echo esc_html( $classroom_building_name ); ?>">
									<span><?php esc_html_e( 'Open Map', 'ubc-vpfo-spaces-pages' ); ?></span>
									<i class="fas fa-location-dot ms-3"></i>
								</a>
							</div>
						</div>
					</div>
					<?php
				}

				if ( $classroom_building_map ) {
					if ( $classroom_is_informal ) {
						?>
						<div class="col-lg-12 classroom-map-button mb-5">
							<a href="<?php echo esc_url( $classroom_building_map ); ?>" class="btn btn-secondary d-inline-block" target="_blank" title="Wayfinding at UBC Map for <?php echo esc_html( $classroom_building_name ); ?>">
								<span><?php esc_html_e( 'Open Map', 'ubc-vpfo-spaces-pages' ); ?></span>
								<i class="fas fa-location-dot ms-3"></i>
							</a>
						</div>
						<?php
					}
					?>
					<div class="<?php echo esc_html( $classroom_map_col_class ); ?>">
						<div class="classroom-map ratio">
							<iframe src="<?php echo esc_url( $classroom_building_map ); ?>" title="Wayfinding Map"></iframe>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</section>

		<section class="classroom-footer mt-9">
			<p><?php echo wp_kses_post( sprintf( 'Find something you don\'t recognize? We\'ve compiled definitions in our <a href="%s" rel="bookmark" title="UBC Learning Spaces glossary">glossary</a>.', 'https://learningspaces.ubc.ca/resources/glossary' ) ); ?></p>

			<div class="pattern-slice position-relative mt-9">
				<div class="pattern-slice-gradient position-absolute h-100 w-100"></div>
			</div>
		</section>

	</div>
</section>

<?php get_footer(); ?>