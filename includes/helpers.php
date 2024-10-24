<?php
// Helper function to split room numbers into numeric and alphabetical parts
function split_room_number( $room ) {
	preg_match( '/^(\d*)(.*)$/', $room, $matches );
	$number_part = $matches[1] ?? ''; // Numeric part (empty string if not present)
	$alpha_part  = $matches[2] ?? ''; // Alphabetical part (empty string if not present)
	return array( $number_part, $alpha_part );
}

// Function to sort and paginate $building_classrooms
function get_sorted_and_paginated_classrooms( $building_classrooms, $page = 1, $per_page = 12 ) {
	// Convert $building_classrooms to an array if it's an array of objects
	$building_classrooms = json_decode( wp_json_encode( $building_classrooms ), true );

	// Sort by 'Room Number'
	usort(
		$building_classrooms,
		function ( $a, $b ) {
			$room_a = $a['fields']['Room Number'] ?? '';
			$room_b = $b['fields']['Room Number'] ?? '';

			list( $num_a, $alpha_a ) = split_room_number( $room_a );
			list( $num_b, $alpha_b ) = split_room_number( $room_b );

			// If both have numeric parts, compare numerically first
			if ( is_numeric( $num_a ) && is_numeric( $num_b ) ) {
				if ( (int) $num_a !== (int) $num_b ) {
					return (int) $num_a - (int) $num_b; // Numeric comparison
				}
				// If numeric parts are equal, compare alphabetically
				return strcasecmp( $alpha_a, $alpha_b );
			}

			// If one has a numeric part and the other doesn't, the one with the number comes first
			if ( is_numeric( $num_a ) ) {
				return -1;
			}
			if ( is_numeric( $num_b ) ) {
				return 1;
			}

			// If neither has a numeric part, compare alphabetically
			return strcasecmp( $room_a, $room_b );
		}
	);

	// Pagination logic
	$offset = ( $page - 1 ) * $per_page;
	return array_slice( $building_classrooms, $offset, $per_page );
}
