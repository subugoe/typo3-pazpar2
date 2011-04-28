<?php

$subjectGroups = array(
	array(
		'name' => 'Geschichte',
		'subjects' => array(
			array(
				'name' => 'Geschichtsschreibung und -theorie',
				'GOKs' => array('PB')
			),
			array(
				'name' => 'Historische Hilfswissenschaften',
				'GOKs' => array('PD')
			),
			array(
				'name' => 'Weltgeschichte (Entdeckungen; Weltkriege etc.)',
				'GOKs' => array('PF')
			),
			array(
				'name' => 'England (epochenübergreifend)',
				'GOKs' => array('POA 000', 'POB 000', 'POD 000', 'POF 000', 'POG 000', 'POH 000', 'POL 000', 'POM 000', 'PON 000', 'POO 000', 'PO 1', 'PO 2', 'PO 3', 'PO 4', 'PO 5', 'PO 6', 'PO 7', 'PO 8', 'PO 9', 'POL')
			),
			array(
				'name' => 'England im Mittelalter',
				'GOKs' => array('POA', 'POM')
			),
			array(
				'name' => 'England in der Frühen Neuzeit',
				'GOKs' => array('POB', 'PON')
			),
			array(
				'name' => 'England im 19. Jahrhundert',
				'GOKs' => array('POD', 'POO')
			),
			array(
				'name' => 'England im 20. Jahrhundert',
				'GOKs' => array('POF', 'POG', 'POH', 'POO')
			),
			array(
				'name' => 'Schottland',
				'GOKs' => array('POP', 'POQ', 'POR', 'POS'),
				'inline' => true
			),
			array(
				'name' => 'Irland',
				'GOKs' => array('POT', 'POV', 'POW', 'POX', 'POY', 'POZ'),
				'inline' => true
			),
			array(
				'name' => 'USA (epochenübergreifend)',
				'GOKs' => array('PVK')
			),
			array(
				'name' => 'USA in der Frühen Neuzeit',
				'GOKs' => array('PVL')
			),
			array(
				'name' => 'USA 1776-1918',
				'GOKs' => array('PVM')
			),
			array(
				'name' => 'USA im 20. Jahrhundert',
				'GOKs' => array('PVN', 'PVO', 'PVP')
			),
			array(
				'name' => 'Kanada',
				'GOKs' => array('PVD', 'PVE', 'PVF', 'PVG'),
				'inline' => true
			),
			array(
				'name' => 'Australien',
				'GOKs' => array('PXB', 'PXC', 'PXD', 'PXE'),
				'inline' => true
			),
			array(
				'name' => 'Neuseeland',
				'GOKs' => array('PXN', 'PXO', 'PXP', 'PXR', 'PXS'),
				'inline' => true
			),
			array(
				'name' => 'Jüdisches Volk im angloamerikanischen Raum',
				'GOKs' => array('PYC 300', 'PYD 300', 'PYE 300', 'PYF 300', 'PYG 300', 'PYC 7', 'PYE 7', 'PYF 7', 'PYG 7')
			),
		),
	),

	array(
		'name' => 'Englische Philologie',
		'GOKs' => array('IA'),
		'subjects' => array (
			array(
				'name' => 'Sprach- und Literaturwissenschaft, Allgemeines',
				'GOKs' => array('IA 2')
			),
			array(
				'name' => 'Sprachwissenschaft',
				'GOKs' => array('IA 3'),
				'inline' => true
			),
			array(
				'name' => 'Anthologien',
				'GOKs' => array('IA 4'),
				'inline' => true
			),
			array(
				'name' => 'Literaturwissenschaft',
				'GOKs' => array('IA 5')
			),
			array(
				'name' => 'Textausgaben und Sekundärliteratur',
				'GOKs' => array('IA 6')
			),
			array(
				'name' => 'Altenglisch',
				'GOKs' => array('IAB'),
				'inline' => true
			),
			array(
				'name' => 'Mittel- und Frühneuenglisch',
				'GOKs' => array('IAD', 'IAE'),
				'inline' => true
			),
			array(
				'name' => 'Schottland',
				'GOKs' => array('IAH'),
				'inline' => true,
				'break' => true
			),
			array(
				'name' => 'Wales',
				'GOKs' => array('IAJ'),
				'inline' => true
			),
			array(
				'name' => 'Irland',
				'GOKs' => array('IAF'),
				'inline' => true
			),
		),
	),
	
	array(
		'name' => 'Amerikanische Philologie',
		'GOKs' => array('IC'),
		'subjects' => array(
			array(
				'name' => 'Sprach- und Literaturwissenschaft, Allgemeines',
				'GOKs' => array('IC 2')
			),
			array(
				'name' => 'Sprachwissenschaft',
				'GOKs' => array('IC 3'),
				'inline' => true
			),
			array(
				'name' => 'Anthologien',
				'GOKs' => array('IC 4'),
				'inline' => true
			),
			array(
				'name' => 'Literaturwissenschaft',
				'GOKs' => array('IC 5')
			),
			array(
				'name' => 'Textausgaben und Sekundärliteratur',
				'GOKs' => array('IC 6')
			),
		),
	),

	array(
		'name' => 'Globales Englisch',
		'GOKs' => array('IB'),
		'subjects' => array(
			array(
				'name' => 'Kanada',
				'GOKs' => array('IBJ'),
				'inline' => true
			),
			array(
				'name' => 'Australien',
				'GOKs' => array('IBB'),
				'inline' => true
			),
			array(
				'name' => 'Neuseeland',
				'GOKs' => array('IBG'),
				'inline' => true,
			),
			array(
				'name' => 'Afrika',
				'GOKs' => array('IBM'),
				'inline' => true,
				'break' => true
			),
			array(
				'name' => 'Karibischer Raum',
				'GOKs' => array('IBO'),
				'inline' => true
			),
			array(
				'name' => 'Malta',
				'GOKs' => array('IBK'),
				'inline' => true
			),
			array(
				'name' => 'Asien, Ozeanien',
				'GOKs' => array('IBR'),
				'inline' => true,
				'break' => true
			),
			array(
				'name' => 'Indischer Subkontinent',
				'GOKs' => array('IBW'),
				'inline' => true
			),
			array(
				'name' => 'Mischsprachen',
				'GOKs' => array('IBY')
			),
		),
	),
	
);

?>
