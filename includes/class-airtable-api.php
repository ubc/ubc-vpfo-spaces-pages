<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

use TANIOS\Airtable\Airtable;

class Airtable_Api {

	public Airtable $airtable;
	public string $site_location;

	const CACHE_TTL      = 3600;
	const ROOMS_PER_PAGE = 10;

	const LOCATION_VAN  = 'van_airtable';
	const LOCATION_OKAN = 'okan_airtable';

	public $cache_prefix = 'airtable_cache_';

	public function __construct( array $settings ) {
		$this->airtable = $this->negotiate_air_table_location( $settings );
	}

	public function get_building_by_slug( string $building_slug ) {
		$params = array(
			'filterByFormula' => sprintf( "AND( slug = '%s' )", $building_slug ),
			'maxRecords'      => 1,
		);

		$response = $this->get( table: 'Buildings', params: $params, request_resource: $building_slug );

		if ( ! $response || empty( $response ) ) {
			return null;
		}

		return $response[0];
	}

	public function get_classrooms_for_building( string $building_code ) {
		// Check if the building code is provided
		if ( ! $building_code ) {
			return null; // No building code provided
		}

		// Query the classrooms based on the building code
		$params = array(
			'filterByFormula' => sprintf( "AND( {Building Code} = '%s', NOT( {Is Hidden} ), NOT( {Is Informal Space} ) )", $building_code ),
		);

		$response = $this->get( table: 'Classrooms', params: $params, request_resource: $building_code );

		if ( ! $response || empty( $response ) ) {
			return null; // No classrooms found
		}

		// Return the list of classrooms
		return $response;
	}

	public function get_building_slugs_for_yoast() {
		$params = array(
			'fields' => array(
				'Slug',
				'Last Modified',
			),
		);

		// Query the Buildings table for only the specified fields
		$response = $this->get( table: 'Buildings', params: $params, request_resource: 'building_slugs' );

		// Check if the response is valid and contains data
		if ( ! $response || empty( $response ) ) {
			return null; // No buildings found or response is empty
		}

		// Return the list of buildings with Slug and Last Modified data
		return $response;
	}

	public function get_building_options_links() {
		$params = array(
			'filterByFormula' => '{Group} = "Building Link"',
			'fields'          => array(
				'Key',
				'Value',
			),
		);

		// Query the Options table for only the specified fields
		$response = $this->get( table: 'Options', params: $params, request_resource: 'building_options_links' );

		// Check if the response is valid and contains data
		if ( ! $response || empty( $response ) ) {
			return null; // No links found or response is empty
		}

		// Return the list of links
		return $response;
	}

	public function get_classroom_by_slug( string $classroom_slug ) {
		$params = array(
			'filterByFormula' => sprintf( "AND( slug = '%s' )", $classroom_slug ),
			'maxRecords'      => 1,
		);

		$response = $this->get( table: 'Classrooms', params: $params, request_resource: $classroom_slug );

		if ( ! $response || empty( $response ) ) {
			return null;
		}

		$classroom = $response[0];

		return $classroom;
	}

	public function get_resources() {
		$params['fields'] = array(
			'File Name',
			'Attachment',
			'Attachment',
			'Category',
		);

		$resources = $this->get( table: 'All Resources', params: $params, request_resource: 'resources' );

		return $resources;
	}

	public function get_classroom_building_slug( string $classroom_building_code ) {
		// Check if the building code is provided
		if ( ! $classroom_building_code ) {
			return ''; // No building code provided
		}

		$params = array(
			'filterByFormula' => sprintf( "AND( {Code} = '%s' )", $classroom_building_code ),
			'maxRecords'      => 1,
		);

		$response = $this->get( table: 'Buildings', params: $params, request_resource: $classroom_building_code );

		if ( ! $response || empty( $response ) ) {
			return null;
		}

		$building      = $response[0];
		$building_slug = $building->fields->{'Slug'};

		return $building_slug;
	}

	public function get_classroom_slugs_for_yoast() {
		$params = array(
			'fields'          => array( 'Slug', 'Last Modified' ),
			'filterByFormula' => 'NOT( {Is Hidden} )',
		);

		// Query the Classrooms table for only the specified fields
		$response = $this->get( table: 'Classrooms', params: $params, request_resource: 'classroom_slugs' );

		// Check if the response is valid and contains data
		if ( ! $response || empty( $response ) ) {
			return null; // No buildings found or response is empty
		}

		// Return the list of buildings with Slug and Last Modified data
		return $response;
	}

	/**
	 * Iterate over all keys in the Airtable Response and sanitize the
	 * values for storage as a WordPress Transient.
	 *
	 * @param mixed $data The Airtable response data.
	 * @return mixed The sanitized data.
	 */
	public function sanitize_for_transient( $data ) {

		if ( ! is_array( $data ) ) {
			return $data;
		}

		foreach ( $data as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->sanitize_for_transient( $value ); // Recursive call for nested arrays
			} elseif ( is_string( $value ) ) {
				if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$value = esc_url_raw( $value );
				} elseif ( is_numeric( $value ) ) {
					$value = intval( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				$value = intval( $value );
			}
		}

		return $data;
	}

	public function get( string $table, array $params, string $request_resource ): mixed {
		$cache_key = $this->get_cache_key( table: $table, params: $params, request_resource: $request_resource );

		if ( get_transient( $cache_key ) ) {
			return get_transient( $cache_key );
		}

		$response = $this->airtable->getContent( $table, $params )->getResponse();

		if ( isset( $response['error'] ) ) {
			throw new \Exception(
				'Invalid Airtable response ' .
				wp_json_encode(
					array(
						'formula'  => $params['filterByFormula'] ?? null,
						'error'    => $response['error'],
						'params'   => $params,
						'response' => $response,
						'table'    => $table,
					)
				)
			);
		}

		// If there's no records the slug is likely wrong.
		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null;
		}

		$sanitized_records = $this->sanitize_for_transient( $response['records'] );
		set_transient( transient: $cache_key, value: $sanitized_records, expiration: self::CACHE_TTL );

		return get_transient( $cache_key );
	}

	/**
	 * @todo issue #3 magically determine the sites location, likely from the current blogs wp_options.
	 *   Likely read from the plugin settings.
	 */
	public function negotiate_air_table_location( array $settings ): Airtable {
		$this->site_location = self::LOCATION_VAN;

		$api_key = $settings['api_key'];

		$base_id = match ( $this->site_location ) {
			self::LOCATION_VAN  => $settings['base_id_van'],
			self::LOCATION_OKAN => $settings['base_id_okan'],
		};

		return new Airtable(
			array(
				'api_key' => $api_key,
				'base'    => $base_id,
			)
		);
	}

	public function get_cache_key( string $table, array $params, string $request_resource ): string {
		$key = sprintf( '%s_%s_%s_%s', $this->site_location, $table, $request_resource, hash( 'xxh32', wp_json_encode( $params ) ) );
		return sprintf( '%s_%s', $this->cache_prefix, $key );
	}
}
