<?php

// add Buldings virtual pages to the Yoast pages sitemap
function add_virtual_page_urls_to_yoast_page_sitemap() {
	$api_key      = get_option( 'UBC_VPFO_AIRTABLE_API_KEY' ) ?? null;
	$base_id_van  = get_option( 'UBC_VPFO_AIRTABLE_BASE_ID_VAN' ) ?? null;
	$base_id_okan = get_option( 'UBC_VPFO_AIRTABLE_BASE_ID_OKAN' ) ?? null;

	if ( ! $api_key || ! $base_id_van || ! $base_id_okan ) {
		return;
	}

	$airtable_api = new \UbcVpfoSpacesPage\Airtable_Api(
		array(
			'api_key'      => $api_key,
			'base_id_van'  => $base_id_van,
			'base_id_okan' => $base_id_okan,
		)
	);

	$buildings  = $airtable_api->get_building_slugs_for_yoast();
	$classrooms = $airtable_api->get_classroom_slugs_for_yoast();

	if ( empty( $buildings ) && empty( $classrooms ) ) {
		return;
	}

	$buildings_array  = json_decode( wp_json_encode( $buildings ), true );
	$classrooms_array = json_decode( wp_json_encode( $classrooms ), true );

	add_filter(
		'wpseo_sitemap_page_content',
		function ( $sitemap_added_items ) use ( $buildings_array, $classrooms_array ) {

			// Sort the buildings array by the 'Slug' field alphabetically
			usort(
				$buildings_array,
				function ( $a, $b ) {
					return strcmp( $a['fields']['Slug'], $b['fields']['Slug'] );
				}
			);

			// Sort the classrooms array by the 'Slug' field alphabetically
			usort(
				$classrooms_array,
				function ( $a, $b ) {
					return strcmp( $a['fields']['Slug'], $b['fields']['Slug'] );
				}
			);

			// Add building URLs to the sitemap
			foreach ( $buildings_array as $building ) {
				if ( ! isset( $building['fields']['Slug'] ) || ! isset( $building['fields']['Last Modified'] ) ) {
					continue;
				}

				$url = esc_url( get_bloginfo( 'url' ) . '/buildings/' . esc_attr( $building['fields']['Slug'] ) );

				// Parse the date and format it to match Yoast's format
				$last_modified_raw = $building['fields']['Last Modified'];
				try {
					$date          = new DateTime( $last_modified_raw );
					$last_modified = $date->format( 'Y-m-d H:i:s O' ); // Full timezone offset
				} catch ( Exception $e ) {
					$last_modified = ''; // Fallback if date parsing fails
				}

				$sitemap_added_items .= "
					<url>
						<loc>{$url}</loc>
						<lastmod>{$last_modified}</lastmod>
					</url>
				";
			}

			// Add classroom URLs to the sitemap
			foreach ( $classrooms_array as $classroom ) {
				if ( ! isset( $classroom['fields']['Slug'] ) || ! isset( $classroom['fields']['Last Modified'] ) ) {
					continue;
				}

				$url = esc_url( get_bloginfo( 'url' ) . '/classrooms/' . esc_attr( $classroom['fields']['Slug'] ) );

				// Parse the date and format it to match Yoast's format
				$last_modified_raw = $classroom['fields']['Last Modified'];
				try {
					$date          = new DateTime( $last_modified_raw );
					$last_modified = $date->format( 'Y-m-d H:i:s O' ); // Full timezone offset
				} catch ( Exception $e ) {
					$last_modified = ''; // Fallback if date parsing fails
				}

				$sitemap_added_items .= "
					<url>
						<loc>{$url}</loc>
						<lastmod>{$last_modified}</lastmod>
					</url>
				";
			}

			return $sitemap_added_items;
		},
		10,
		1
	);
}

add_action( 'init', 'add_virtual_page_urls_to_yoast_page_sitemap' );
