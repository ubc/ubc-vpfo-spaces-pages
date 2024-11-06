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

	public function get( string $table, array $params, string $request_resource ): mixed {
		$cache_key = $this->get_cache_key( table: $table, params: $params, request_resource: $request_resource );

		if ( get_transient( $cache_key ) ) {
			return get_transient( $cache_key );
		}

		$response = $this->airtable->getContent( $table, $params )->getResponse();

		// If there's no records then we the slug is likely wrong.
		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null;
		}

		set_transient( transient: $cache_key, value: $response['records'], expiration: self::CACHE_TTL );

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
