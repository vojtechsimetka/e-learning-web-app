<?php
/**
 * ITU project - e-learning system for czech plants
 * Drill
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   drill.php
 */

use Nette\Diagnostics\Debugger;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME);

$TMPL->setFile('./latte/drill.latte');

$taxonomy = $DB->table('taxonomy')->get($TAXONOMY);
$TMPL->taxonomy = $taxonomy;

$plants = $DB->table('plant')->where('taxonomy_id', $TAXONOMY)->order('id')->fetchPairs('id');

if (isset($ID))
{// Get first plant
	while (key($plants) != $ID) next($plants);
}
$plant = current($plants);
$TMPL->plant = $plant;
$TMPL->next = get_next($plants);
$TMPL->prev = get_prev($plants);

// Fetch 3 bad options
$options = $DB
	->table('option')
	->where('id != ?', $plant->name)
	->order('RAND()')
	->limit(3)
	->fetchPairs('id');
// Add correct option
$options[$plant->id] = $plant->ref('option', 'name');
shuffle($options);

foreach ($options as $key => $option)
{// Get position of correct option
	if ($option->id == $plant->name)
	{
		$TMPL->correct = $key + 1;
		break;
	}
}
$TMPL->options = $options;

echo $TMPL;
