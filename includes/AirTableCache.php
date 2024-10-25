<?php

trait AirTableCache
{

    const LOCATION_VAN = 'van_airtable';
    const LOCATION_OKAN = 'okan_airtable';

    public $cachePrefix = 'airtable_cache_';

    public function get($location, $table, $params = array())
    {
        if ($location === self::LOCATION_VAN) {
            $request = $this->van_airtable;
        }

        if ($location === self::LOCATION_OKAN) {
            $request = $this->okan_airtable;
        }

        return $request->getContent($table, $params)->getResponse();
    }
}
