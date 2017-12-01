<?php
/**
 * Expiry Content Message
 *
 * If you wish to override this file, create a file called
 * 'view-expiry-content-message.php' in a folder called 'template-parts' of your
 * theme.
 *
 * Note that the message is passed to this file in a variable called ''$message'.
 *
 * @package mkdo\content_expiry_date
 */

// Here is the HTML output, this can be styled however.
// Do not alter this file, instead duplicate it to 'template-parts'
// in your theme.
?>

<aside class="message message-warning">
	<?php echo wp_kses_post( apply_filters( 'the_content', $message ) );?>
</aside>
