<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

use AirTableCache;
use TANIOS\Airtable\Airtable;

class Airtable_Api {

    use AirTableCache;

	private $van_airtable;
	private $okan_airtable;

	const CACHE_TTL      = 3600;
	const ROOMS_PER_PAGE = 10;

	private static $campus_mapping = array(
		'vancouver' => 'van_airtable',
		'okanagan'  => 'okan_airtable',
	);

	public function __construct() {
		$api_key      = UBC_VPFO_FIND_A_SPACE_AIRTABLE_API_KEY;
		$van_base_id  = UBC_VPFO_FIND_A_SPACE_AIRTABLE_BASE_ID_VAN;
		$okan_base_id = UBC_VPFO_FIND_A_SPACE_AIRTABLE_BASE_ID_OKAN;

		$this->van_airtable = new Airtable(
			array(
				'api_key' => $api_key,
				'base'    => $van_base_id,
			)
		);

		$this->okan_airtable = new Airtable(
			array(
				'api_key' => $api_key,
				'base'    => $okan_base_id,
			)
		);
	}

	public function get_building_by_slug( string $building_slug ) {
		$params = array(
			'filterByFormula' => sprintf( "AND( slug = '%s' )", $building_slug ),
			'maxRecords'      => 1,
		);

        $response  = $this->get('van_airtable', 'Buildings', $params );

		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null;
		}

		$building = $response['records'][0];

		return $building;
	}

	public function get_classrooms_for_building( string $building_code ) {
		// Check if the building code is provided
		if ( ! $building_code ) {
			return null; // No building code provided
		}

		// Query the classrooms based on the building code
		$params = array(
			'filterByFormula' => sprintf( "AND( {Building Code} = '%s' )", $building_code ),
		);

		$request  = $this->van_airtable->getContent( 'Classrooms', $params );
		$response = $request->getResponse();

		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null; // No classrooms found
		}

		// Return the list of classrooms
		return $response['records'];
	}

	public function get_classroom_by_slug( string $classroom_slug ) {
		$params = array(
			'filterByFormula' => sprintf( "AND( slug = '%s' )", $classroom_slug ),
			'maxRecords'      => 1,
		);

		$request  = $this->van_airtable->getContent( 'Classrooms', $params );
		$response = $request->getResponse();

		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null;
		}

		$classroom = $response['records'][0];

		return $classroom;
	}
}
