<?php

namespace UbcVpfoSpacesPage;

defined('ABSPATH') || exit;

use TANIOS\Airtable\Airtable;

class Airtable_Api
{

    public Airtable $airtable;
    public string $site_location;
    private $van_airtable;
    private $okan_airtable;

    const CACHE_TTL = 3600;
    const ROOMS_PER_PAGE = 10;

    const LOCATION_VAN = 'van_airtable';
    const LOCATION_OKAN = 'okan_airtable';

    public $cache_prefix = 'airtable_cache_';

    private static $campus_mapping = array(
        'vancouver' => 'van_airtable',
        'okanagan'  => 'okan_airtable',
    );

    public function __construct()
    {
        $this->airtable = $this->negotiateAirTableLocation();
    }

    public function get_building_by_slug(string $building_slug)
    {
        $params = array(
            'filterByFormula' => sprintf("AND( slug = '%s' )", $building_slug),
            'maxRecords'      => 1,
        );

        $response = $this->get(table: 'Buildings', params: $params, resource: $building_slug);

        if (!$response['records'] || empty($response['records'])) {
            return null;
        }

        $building = $response['records'][0];

        return $building;
    }

    public function get_classrooms_for_building(string $building_code)
    {
        // Check if the building code is provided
        if (!$building_code) {
            return null; // No building code provided
        }

        // Query the classrooms based on the building code
        $params = array(
            'filterByFormula' => sprintf("AND( {Building Code} = '%s' )", $building_code),
        );

        $response = $this->get(table: 'Classrooms', params: $params, resource: $building_code);

        if (!$response || empty($response)) {
            return null; // No classrooms found
        }

        // Return the list of classrooms
        return $response;
    }

    public function get_classroom_by_slug(string $classroom_slug)
    {
        $params = array(
            'filterByFormula' => sprintf("AND( slug = '%s' )", $classroom_slug),
            'maxRecords'      => 1,
        );

        $response = $this->get(table: 'Classrooms', params: $params, resource: $classroom_slug);

        if (!$response || empty($response)) {
            return null;
        }

        $classroom = $response[0];

        return $classroom;
    }

    public function get(string $table, array $params, string $resource): mixed
    {
        $cache_key = $this->getCacheKey(table: $table, params: $params, resource: $resource);

        if (get_transient($cache_key)) {
            return get_transient($cache_key);
        }

        $response = $this->airtable->getContent($table, $params)->getResponse();

        // If there's no records then we the slug is likely wrong.
        if (!$response['records'] || empty($response['records'])) {
            return null;
        }

        return set_transient(transient: $cache_key, value: $response['records'], expiration: self::CACHE_TTL);
    }

    /**
     * @todo issue #3 magically determine the sites location, likely from the current blogs wp_options.
     *   Likely read from the plugin settings.
     */
    public function negotiateAirTableLocation(): Airtable
    {
        $this->site_location = self::LOCATION_VAN;

        $api_key = UBC_VPFO_FIND_A_SPACE_AIRTABLE_API_KEY;

        $base_id = match ($this->site_location) {
            self::LOCATION_VAN  => UBC_VPFO_FIND_A_SPACE_AIRTABLE_BASE_ID_VAN,
            self::LOCATION_OKAN => UBC_VPFO_FIND_A_SPACE_AIRTABLE_BASE_ID_OKAN,
        };

        return new Airtable(
            array(
                'api_key' => $api_key,
                'base'    => $base_id,
            )
        );
    }

    public function getCacheKey(string $table, array $params, string $resource): string
    {
        $key = sprintf('%s_%s_%s_%s', $this->site_location, $table, $resource, hash('xxh32', wp_json_encode($params)));
        return sprintf('%s_%s', $this->cache_prefix, $key);
    }


}
