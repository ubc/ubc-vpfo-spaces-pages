<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

use TANIOS\Airtable\Airtable;

class Airtable_Api {
	private $airtable;

	public function __construct() {
		$api_key = UBC_VPFO_SPACES_PAGE_AIRTABLE_API_KEY;
		$base_id = UBC_VPFO_SPACES_PAGE_AIRTABLE_BASE_ID;

		$this->airtable = new Airtable(
			array(
				'api_key' => $api_key,
				'base'    => $base_id,
			)
		);
	}

	public function get_building_by_slug( string $building_slug ) {
		$params = array(
			'filterByFormula' => sprintf( "AND( slug = '%s' )", $building_slug ),
			'maxRecords'      => 1,
		);

		$request  = $this->airtable->getContent( 'Buildings', $params );
		$response = $request->getResponse();

		if ( ! $response['records'] || empty( $response['records'] ) ) {
			return null;
		}

		$building = $response['records'][0];

		dd( $building );

		return $building;
	}
}
