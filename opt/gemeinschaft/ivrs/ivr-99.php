<?php


$ivr = array(
	'dp' => array(
		'play /var/lib/asterisk/sounds/de/demo-congrats',
		'play /var/lib/asterisk/sounds/de/demo-instruct'
	),
	'options' => array(
		'2' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/tt-weasels',
				'hangup'
			)
		),
		'500' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/demo-abouttotry',
				'app Dial(IAX2/guest@misery.digium.com/s@default)',
				'play /var/lib/asterisk/sounds/de/demo-nogo',
				'hangup'
			)
		),
		'600' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/demo-echotest',
				'app Echo()',
				'play /var/lib/asterisk/sounds/de/demo-echodone',
				'hangup'
			)
		),
		'1234' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/tt-monkeys',
				'hangup'
			)
		),
		'8500' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/vm-changeto',
				'play /var/lib/asterisk/sounds/de/vm-onefor',
				'play /var/lib/asterisk/sounds/de/vm-INBOX',
				'play /var/lib/asterisk/sounds/de/vm-messages',
				'play /var/lib/asterisk/sounds/de/digits/2',
				'play /var/lib/asterisk/sounds/de/vm-for',
				'play /var/lib/asterisk/sounds/de/vm-Old',
				'play /var/lib/asterisk/sounds/de/vm-messages'
			),
			'options' => array(
				'1' => array(
					'dp' => array(
						'play /var/lib/asterisk/sounds/de/digits/1',
						'hangup'
					)
				),
				'2' => array(
					'dp' => array(
						'play /var/lib/asterisk/sounds/de/digits/2',
						'hangup'
					)
				),
				'#' => array(
					'dp' => array(
						'play /var/lib/asterisk/sounds/de/gemeinschaft/auf-wiedersehen',
						'hangup'
					)
				),
				't' => array(
					'dp' => array(
						'play /var/lib/asterisk/sounds/de/gemeinschaft/auf-wiedersehen',
						'hangup'
					)
				),
				'i' => array(
					'dp' => array(
						'play /var/lib/asterisk/sounds/de/gemeinschaft/nein',
						'hangup'
					)
				)
			)
		),
		'#' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/gemeinschaft/auf-wiedersehen',
				'hangup'
			)
		),
		't' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/gemeinschaft/auf-wiedersehen',
				'hangup'
			)
		),
		'i' => array(
			'dp' => array(
				'play /var/lib/asterisk/sounds/de/gemeinschaft/nein',
				'hangup'
			)
		)
	)
);


?>