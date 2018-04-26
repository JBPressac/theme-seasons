<?php echo head(array('title' => metadata('item', array('Dublin Core', 'Title')),'bodyclass' => 'items show')); ?>

<h1><?php echo metadata('item', array('Dublin Core', 'Title')); ?></h1>

<?php
	$dc_contributor = metadata('item', array('Dublin Core', 'Contributor'), array('all' => true));
	$dc_coverage = metadata('item', array('Dublin Core', 'Coverage'), array('all' => true));
	$dc_creator = metadata('item', array('Dublin Core', 'Creator'), array('all' => true));
	$dc_date = metadata('item', array('Dublin Core', 'Date'), array('all' => true));
	$dc_date_created = metadata('item', array('Dublin Core', 'Date Created'), array('all' => true));
	$dc_description = metadata('item', array('Dublin Core', 'Description'), array('all' => true));
	$dc_identifier = metadata('item', array('Dublin Core', 'Identifier'), array('all' => true));
	$dc_language = metadata('item', array('Dublin Core', 'Language'), array('all' => true));
	$dc_medium = metadata('item', array('Dublin Core', 'Medium'), array('all' => true));
	$dc_provenance = metadata('item', array('Dublin Core', 'Provenance'), array('all' => true));
	$dc_publisher = metadata('item', array('Dublin Core', 'Publisher'), array('all' => true));
	$dc_relation = metadata('item', array('Dublin Core', 'Relation'), array('all' => true));
	$dc_rights = metadata('item', array('Dublin Core', 'Rights'), array('all' => true));
	$dc_rights_holder = metadata('item', array('Dublin Core', 'Rights Holder'), array('all' => true));
	$dc_source = metadata('item', array('Dublin Core', 'Source'), array('all' => true));
	$dc_spatial_coverage = metadata('item', array('Dublin Core', 'Spatial Coverage'), array('all' => true));
	$dc_subject = metadata('item', array('Dublin Core', 'Subject'), array('all' => true));
	$dc_temporal_coverage = metadata('item', array('Dublin Core', 'Temporal Coverage'), array('all' => true));
	$dc_type = metadata('item', array('Dublin Core', 'Type'), array('all' => true));

	$collection = get_collection_for_item();
	if ($collection){
		$collection_id = metadata($collection, 'id');
	}

	$collection_name = metadata('item', 'collection_name');
	$item_type = metadata('item', 'item_type_name'); # http://omeka.readthedocs.org/en/latest/Reference/libraries/globals/metadata.html#id1

	// function conversion_date(&$item, $key)
	function conversion_date(&$item)
	{
		/* http://omeka.readthedocs.org/en/latest/Tutorials/i18n.html#dates-and-times */
		/* http://framework.zend.com/manual/1.12/fr/zend.date.additional.html */
		// Attention, Zend_Date::isDate considère qu'un intervale de date au format ISO 8601 (1756-06-12/1759-12-08)
		// est une date alors que format_date ne sait pas convertir l'intervale
		// en 12 juin 1756 - 8 décembre 1759.

		if (Zend_Date::isDate($item, 'yyyy-MM-dd')) {
			// echo Zend_Date::isDate($item, 'yyyy-MM-dd');
    		$item = format_date(strtotime($item));
		} elseif(Zend_Date::isDate($item, 'yyyy-MM')) {
			$date = new Zend_Date($item, 'yyyy.MM');
			$item = $date->toString('MMMM yyyy');
		} elseif(Zend_Date::isDate($item, 'yyyy')) {
			$date = new Zend_Date($item, 'yyyy');
			$item = $date->toString('yyyy');
		}
	}

	// $item est un tableau
	// On passe en paramètre une date (1789-07-14) ou un intervale de date au format ISO 8601 (1756-06-12/1759-12-08)
	// On récupère un tableau que l'on peut afficher avec echo implode(" / ", $item);
	function split_duration(&$item, $key)
	{
		// $item est une string	à l'interieur de la fonction split_duration
		// echo "type de item: ", gettype($item), "\n";
		// $dates est un array
		$dates = explode('/', $item);
		// echo "type de dates: ", gettype($dates), "\n";
		// echo $dates[1];
		array_walk($dates, 'conversion_date');
		// https://fr.wikipedia.org/wiki/Tiret#Tiret_moyen
		$item = implode(' &ndash; ', $dates);
		// echo conversion_date($dates[0]);
		// $item = $dates;
	}

	# Test pour savoir si l'item provient du moissonnage de Cocoon
	# $dc_identifier est un tableau (utiliser print_r() pour afficher un array)
	# http://stackoverflow.com/questions/8627334/how-to-search-in-array-with-preg-match
	# http://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql/les-expressions-regulieres-partie-1-2
	$item_from_cocoon = false;
	$cocoon_mp3_file = '';
	$matches_item_from_cocoon  = preg_grep ('/cocoon.huma-num.fr/i', $dc_identifier);
	if (empty($matches_item_from_cocoon))
		{
			$item_from_cocoon = false;
			}
	else
		{
			$item_from_cocoon = true;
			$cocoon_mp3_file = current(preg_grep ('/mp3/i', $dc_identifier));
			$cocoon_document_url = current(preg_grep ('/crdo\/ark:/i', $dc_identifier));
			$dc_rights  = preg_grep ('/Copyright/i', $dc_rights);
			$dc_rights = preg_replace('/Copyright \(c\)/i', '', $dc_rights);
			}

	# Test pour savoir si l'item provient du moissonnage de la médiathèque de Dastum
	$item_from_dastum = false;
	$matches_item_from_dastum  = preg_grep ('/www.mediatheque.dastum.net/i', $dc_identifier);
	if (empty($matches_item_from_dastum))
		{
			$item_from_dastum = false;
			}
	else
		{
			$item_from_dastum = true;
			}
?>

<div id="primary">

    <?php if ((get_theme_option('Item FileGallery') == 0) && metadata('item', 'has files')): ?>
    <?php echo files_for_item(array('imageSize' => 'fullsize')); ?>
    <?php endif; ?>

	<?php
		$files = $item->Files;
		if ($files) {
			// Lien pour télécharger le fichier PDF
			if ($collection_name == 'Registres du bagne de Brest') {
				echo $this->universalViewer($item);
				$html =	'
				<script type="text/javascript">
					function showDiv() {
						document.getElementById(\'pdf-item-download\').style.display = "block";
					}
				</script>

				<div>
					<p>Prenez connaissance et approuvez les <a href="/conditions-generales-utilisation" target="_blank">conditions générales d\'utilisation</a> (CGU) avant de pouvoir télécharger les documents au format PDF.</p>
					<p><input type="checkbox" name="answer" value="Show Div" onclick="showDiv()" /> J\'ai lu et j\'approuve les <a href="/conditions-generales-utilisation" target="_blank">conditions générales d\'utilisation</a>.</p>
				</div>
				<div id="pdf-item-download" style="display:none;">
				';
			}
			else {
				// Ajout pour utilisation du plugin Bookreader
				// fire_plugin_hook('book_reader_item_show', array('view' => $this,'item' => $item,));
				echo $this->getBookReader();
				$html =	'<div id="pdf-item-download">';
			}
			foreach ($files as $file) {
				if ($file->mime_type == 'application/pdf'):
					$linkAttrs = array('href' => $file->getWebPath('original'));
					$html .= 'Télécharger les documents au format PDF : <a ' . tag_attributes($linkAttrs) . '>' . $file->filename .
					'</a> ('. $file->size . ' octets)';
				endif;
			}
			$html .= '</div>';
			echo $html;
		}
	?>

    <?php if ($item_from_cocoon): ?>
    	<div><audio src="<?php echo $cocoon_mp3_file ?>" controls width="700"></audio></div>
    <?php endif; ?>

    <?php if ($dc_identifier && $item_from_dastum): ?>
    	<div>Cet enregistrement sonore peut-être consulté en ligne sur la médiathèque de Dastum en suivant ce lien :
    	<a href="<?php echo implode(" / ", $dc_identifier); ?>"><?php echo implode(" / ", $dc_identifier); ?></a></div>
    <?php endif; ?>

    <div>
    	<?php if ($item_from_cocoon || $item_from_dastum): ?>
    		<h2>Informations sur l'enregistrement sonore</h2>
		<?php else: ?>
    		<h2>Informations sur les documents originaux</h2>
    	<?php endif; ?>

    	<?php if ($dc_creator && !$item_from_cocoon && !$item_from_dastum): ?>
    		<h4>Auteur(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_creator); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_relation && $item_from_dastum): ?>
			<div class="element-text">Cet enregistrement fait partie de l'enquête : <?php echo implode(" / ", $dc_relation); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_creator && $item_from_dastum): ?>
    		<h4>Collecteur(s) et interprète(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_creator); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_contributor): ?>
    		<h4>Contributeur(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_contributor); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_spatial_coverage && $collection_name == 'Barzaz Bro-Leon'): ?>
    		<h4>Commune d'expédition</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_spatial_coverage); ?></div>
    	<?php endif; ?>

    	<?php
    		if ($dc_coverage && $item_from_cocoon):
    			$matches  = preg_grep ('/France/i', $dc_coverage);
    			$matches = preg_replace('/\(spatial\)/i', '', $matches);
    	?>
    		<h4>Lieu de l'enregistrement</h4>
			<div class="element-text"><?php echo implode(" / ", $matches); ?></div>
    	<?php endif; ?>

    	<?php
    		if ($dc_coverage && $item_from_dastum): ?>
    		<h4>Lieu de l'enregistrement</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_coverage); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_date):?>
    		<?php if ($collection_name == 'Barzaz Bro-Leon'): ?>
    			<h4>Date d'expédition par voie postale des documents originaux</h4>
				<div class="element-text"><?php array_walk($dc_date, 'split_duration'); echo implode(" / ", $dc_date); ?></div>
			<?php elseif ($item_from_cocoon || $item_from_dastum): // elseif (in_array("Sound", $dc_type)): ?>
				<?php if($item_from_cocoon):
					// Cocoon expose dans le champ dc:date les types de dates suivantes :
					// 2010-07-09 (available)
					// 2010-07-09T03:26:21+02:00 (issued)
					// 1990 (created)
					// start=1989; end=1991 (created)
					// On ne récupère que les dates (created). Les dates indiquées sous forme d'intervelles n'étant pas au format ISO 8601
					// on affiche telles quelles les dates Cocoon sans appliquer split_duration().
					// Il se peut également que la date (created) ne soit pas disponible.
					$matches  = preg_grep ('/(created)/i', $dc_date);
					$matches = preg_replace('/\(created\)/i', '', $matches);
				?>
					<h4>Date de l'enregistrement</h4>
					<div class="element-text"><?php echo implode(" / ", $matches); ?></div>
				<?php else:?>
					<h4>Date de l'enregistrement</h4>
					<div class="element-text"><?php echo implode(" / ", $dc_date); ?></div>
				<?php endif; ?>
			<?php else:?>
   	 		<h4>Date</h4>
 				<div class="element-text"><?php array_walk($dc_date, 'split_duration'); echo implode(" / ", $dc_date); ?></div>
 			<?php endif; ?>
		<?php endif; ?>

    	<?php if ($dc_description && $collection_name == 'Barzaz Bro-Leon'): ?>
    		<h4>Description des documents originaux</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_description); ?></div>
    	<?php elseif ($dc_description && $collection_name != 'Barzaz Bro-Leon'): ?>
    		<?php if ($item_from_cocoon || $item_from_dastum): ?>
    			<h4>Description de l'enregistrement</h4>
    		<?php else: ?>
	    		<h4>Description du document original</h4>
    		<?php endif; ?>
			<div class="element-text"><?php echo implode(" / ", $dc_description); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_subject && $collection_name == 'Barzaz Bro-Leon'): ?>
    		<h4>Chansons et autres pièces contenues dans le(s) document(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_subject); ?></div>
		<?php elseif ($dc_subject && $collection_name != 'Barzaz Bro-Leon'): ?>
			<?php if (!$item_from_cocoon): ?>
    		<h4>Sujets du document original</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_subject); ?></div>
			<?php endif; ?>
    	<?php endif; ?>

    	<?php if ($dc_coverage && $collection_name != 'Barzaz Bro-Leon'): ?>
    		<?php if (!$item_from_cocoon && !$item_from_dastum): ?>
    			<h4>Contexte</h4>
				<div class="element-text"><?php echo implode(" / ", $dc_coverage); ?></div>
			<?php endif; ?>
    	<?php endif; ?>

    	<?php if ($dc_spatial_coverage && $collection_name != 'Barzaz Bro-Leon'): ?>
    		<h4>Contexte spatial</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_spatial_coverage); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_provenance): ?>
    		<h4>Origine des documents originaux</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_provenance); ?></div>
    	<?php endif; ?>

    <?php if ($dc_identifier && !$item_from_cocoon && !$item_from_dastum): ?>
    	<h4>Cote du document original</h4>
		<div class="element-text"><?php echo implode(" / ", $dc_identifier); ?></div>
    <?php endif; ?>

    	<?php if ($dc_medium && $collection_name == 'Barzaz Bro-Leon'): ?>
    		<h4>Nature des documents originaux</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_medium); ?></div>
		<?php elseif ($dc_medium && $collection_name != 'Barzaz Bro-Leon'): ?>
    		<h4>Nature du document original</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_medium); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_date_created): ?>
    		<h4>Date de mise en ligne de la version numérique</h4>
			<div class="element-text"><?php echo implode(" / ", $$dc_date_created); ?></div>
    	<?php endif; ?>

    	<?php
			if ($dc_language && !$item_from_cocoon && !$item_from_dastum):
				$patterns = array();
				$patterns[0] = '/^fre$/';
				$patterns[1] = '/^fra$/';
				$patterns[2] = '/^bre$/';
				$patterns[3] = '/^eng$/';
				$replacements = array();
				$replacements[3] = 'Français';
				$replacements[2] = 'Français';
				$replacements[1] = 'Breton';
				$replacements[0] = 'Anglais';
				$dc_language = preg_replace($patterns, $replacements, $dc_language);
    	?>
    		<h4>Langue(s) utilisées dans le(s) document(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_language); ?></div>
    	<?php endif; ?>

    	<?php
			if ($dc_language && $item_from_cocoon):
				# Suppression des codes langues sur 3 caractères
				$matches  = preg_grep('/^[a-z]{3}$/i', $dc_language, PREG_GREP_INVERT);
    	?>
    		<h4>Langue(s) parlée(s) dans l'enregistrement</h4>
			<div class="element-text"><?php echo implode(" / ", $matches); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_language && $item_from_dastum): ?>
    		<h4>Langue(s) parlée(s) dans l'enregistrement</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_language); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_source && $item_from_dastum): ?>
    		<h4>Cote du disque compact (CD) audio de conservation</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_source); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_publisher): ?>
    		<h4>Éditeur(s)</h4>
			<div class="element-text"><?php echo implode(" / ", $dc_publisher); ?></div>
    	<?php endif; ?>

    	<?php if ($dc_rights_holder || ($item_from_cocoon || $item_from_dastum && $dc_rights)): ?>
    		<h4>Droits d'auteur</h4>
    		<?php if ($item_from_cocoon || $item_from_dastum): ?>
				<div class="element-text"><?php echo implode(" / ", $dc_rights); ?></div>
			<?php else: ?>
				<div class="element-text"><?php echo implode(" / ", $dc_rights_holder); ?></div>
			<?php endif; ?>
    	<?php endif; ?>

    	<?php if ($item_from_cocoon && $cocoon_document_url) {
    		$linkAttrs = array('href' => $cocoon_document_url);
			$html = '<p>Cet enregistrement est hébergé sur la plateforme <a ' . tag_attributes($linkAttrs) . '>' .
				'COllections de COrpus Oraux Numériques</a>.</p>';
			echo $html;
    		}
    	?>

    </div>

</div><!-- end primary -->

<aside id="sidebar">

    <!-- If the item belongs to a collection, the following creates a link to that collection. -->
    <?php if (metadata('item', 'Collection Name')): ?>
    <div id="collection" class="element">
        <h2><?php echo __('Collection'); ?></h2>
        <div class="element-text"><p><?php echo link_to_collection_for_item(); ?></p></div>
    </div>
    <?php endif; ?>

    <!-- The following prints a list of all tags associated with the item -->
    <?php if (metadata('item', 'has tags')): ?>
    <div id="item-tags" class="element">
        <h2><?php echo __('Tags'); ?></h2>
        <div class="element-text"><?php echo tag_string('item'); ?></div>
    </div>
    <?php endif;?>

	<?php fire_plugin_hook('public_items_show', array('view' => $this, 'item' => $item)); ?>

    <!-- The following prints a citation for this item. -->
    <div id="item-citation" class="element">
        <h2><?php echo __('Citation'); ?></h2>
        <div class="element-text"><?php echo metadata('item', 'citation', array('no_escape' => true)); ?></div>
    </div>

</aside>

<ul class="item-pagination navigation">
    <li id="previous-item" class="previous"><?php echo link_to_previous_item_show(); ?></li>
    <li id="next-item" class="next"><?php echo link_to_next_item_show(); ?></li>
</ul>

<?php echo foot(); ?>
