<?php
/**
 * ITU project - e-learning system for czech plants
 * Plant look over
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   show.php
 */

use Nette\Diagnostics\Debugger;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME);

$TMPL->setFile('./latte/show.latte');

$taxonomy = $DB->table('taxonomy')->get($TAXONOMY);
$TMPL->taxonomy = $taxonomy;

$plants = $DB->table('plant')->where('taxonomy_id', $TAXONOMY)->order('id')->fetchPairs('id');

if (isset($ID))
{// Get first plant
	while (key($plants) != $ID) next($plants);
}
$TMPL->plant = current($plants);
$TMPL->next = get_next($plants);
$TMPL->prev = get_prev($plants);

echo $TMPL;
