<?php
foreach ( $errors as $error ):
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo awpcp_print_error( $error );
endforeach;
