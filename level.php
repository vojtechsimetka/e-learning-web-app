<?php
/**
 * ITU project - e-learning system for czech plants
 * Level selection
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   level.php
 */

use Nette\Diagnostics\Debugger;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME . 'access.php');

$TMPL->setFile('./latte/level.latte');

$TMPL->easy_inactive = $DB
	->table('exam')
	->where('user_id', $USER->identity->id)
	->where('taxonomy_id', $TAXONOMY)
	->where('level', 1)
	->fetch();

$TMPL->hard_inactive = $DB
	->table('exam')
	->where('user_id', $USER->identity->id)
	->where('taxonomy_id', $TAXONOMY)
	->where('level', 2)
	->fetch();

if ($ACTION == 'result')
{// Invert meaning
	$TMPL->easy_inactive = !$TMPL->easy_inactive;
	$TMPL->hard_inactive = !$TMPL->hard_inactive;
}

echo $TMPL;
