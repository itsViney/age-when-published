<?php
/**
 * Render the Age When Published block.
 *
 * @param array $attributes Block attributes.
 * @return string Block content.
 */
$post_date = get_the_date( 'Y-m-d' );
$age       = calculate_age_when_published( $post_date );
?>
<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $age; ?>
</p>
