<?php

	$mail = isset( $_GET['email'] ) ? $_GET['email'] : '';
	$from = isset( $_GET['from'] ) ? $_GET['from'] : 'from@example.com';

	$method = isset( $_GET['method'] ) ? $_GET['method'] : 'mail';

	$wp1 = __DIR__ . '/../wp-load.php';
	$wp2 = __DIR__ . '/../wp/wp-load.php';

	if ( ! $mail ) {
		die( 'Define a email address by using ?email=[mymail], <a href="?email=">easy clicker</a>' );
	}

	function ee( $message ) {
		echo $message, "<br />\n";
	}

	ee( 'Using method "' . $method . '", you can choose "mail", "wp_mail"/"wp", "phpmailer"' );
	ee( '' );

	switch ( $method ) {

		default:
		case 'mail':
			ee( 'Mailing with PHP mail() function' );
			mail( $mail, 'Mail with PHP mail()', 'This is a very simple test, just using PHP mail function' );

			ee( 'Mailing with PHP mail() and From header' );
			mail( $mail, 'Mail with PHP mail() and From header', 'This mail should have a correct From address, it could have a wrong Sender though', "From: {$from}\r\n" );

			ee( 'Mailing with PHP mail() and From header using -f flag' );
			mail( $mail, 'Mail with PHP mail() and From header and -f flag', 'This mail should have a correct From address, it should have a correct Sender', "From: {$from}\r\n", "-f{$from}" );

			break;

		case 'wp_mail':
		case 'wp':

			ee( '' );
			ee( 'Loading WordPress environment' );
			if ( file_exists( $wp1 ) ) {
				require_once( $wp1 );
			} else if ( file_exists( $wp2 ) ) {
				require_once( $wp2 );
			} else {
				die( 'Failed loading WordPress env' );
			}

			ee( 'Sending mail with WordPress wp_mail() function' );
			wp_mail( $mail, 'Mail with WordPress wp_mail() function', 'Your mileage may very based on your configuration and plugins' );

			ee( 'Sending mail from WordPress wp_mail() function with From header' );
			wp_mail( $mail, 'Mail with WordPress wp_mail() function with From header', 'Your mileage may very based on your configuration and plugins', "From: {$from}\r\n" );

			ee( 'Adding a filter to set the $phpmailer->Sender' );
			add_action( 'phpmailer_init', function( $params ) {
				// If Sender is invalid we set it to the value of From
				if ( filter_var( $params->Sender, FILTER_VALIDATE_EMAIL ) !== true ) {
					$params->Sender = $params->From;
				}
			} );

			ee( 'Sending another mail with WordPress wp_mail function' );
			wp_mail( $mail, 'Mail with WordPress wp_mail() function with From header after filter', 'This should have a correct Sender in the headers', "From: {$from}\r\n" );
			break;

		var_dump( class_exists( 'PHPMailer' ) );
		case 'phpmailer':

			ee( '' );
			ee( 'Loading PHPMailer version 5.1.0' );
			require_once( __DIR__ . '/class.phpmailer.php' );

			$mail1 = new PHPMailer();

			ee( 'Sending mail with PHPMailer' );
			$mail1->addAddress( $mail );  // Add a recipient

			$mail1->Subject = 'Sending mail with PHPMailer';
			$mail1->Body    = 'This mail should be a simple PHPMailer mail';

			if( ! $mail1->send() ) {
				ee( 'Failed sending mail' );
				exit;
			}

			$mail2 = new PHPMailer();

			ee( 'Sending mail with From address' );
			$mail2->addAddress( $mail );  // Add a recipient
			$mail2->From = $from;

			$mail2->Subject = 'Sending mail with From';
			$mail2->Body    = 'This mail should be a PHPMailer mail with a correct From but maybe not a correct Sender.';

			if( ! $mail2->send() ) {
				ee( 'Failed sending mail' );
				exit;
			}

			$mail3 = new PHPMailer();

			ee( 'Sending mail with From address and Sender' );
			$mail3->addAddress( $mail );  // Add a recipient
			$mail3->From = $from;
			$mail3->Sender = $from;

			$mail3->Subject = 'Sending mail with From and Sender';
			$mail3->Body    = 'This mail should be a PHPMailer mail with a correct From and a correct Sender.';

			if( ! $mail3->send() ) {
				ee( 'Failed sending mail' );
				exit;
			}

	}