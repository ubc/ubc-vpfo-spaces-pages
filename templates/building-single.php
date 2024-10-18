<?php get_header(); 

$building = $args['building'];
$building_fields = $building->fields;

$building_name = isset($building_fields->{'Building Name'}[0]) ? $building_fields->{'Building Name'}[0] : null;
?>

<div class="vpfo-spaces-page">
    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-lg-6">
                <?php echo $building_name; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>