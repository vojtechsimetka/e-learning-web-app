<?php
/**
 * ITU project - e-learning system for czech plants
 * Test result
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   result.php
 */

use Nette\Diagnostics\Debugger;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME . 'access.php');

$TMPL->setFile('./latte/result.latte');

// Load exam description
$exam = $DB
	->table('exam')
	->where('user_id', $USER->identity->id)
	->where('taxonomy_id', $TAXONOMY)
	->where('level', $LEVEL)
	->fetch();

$ncorrect = $DB
	->table('answer')
	->where('exam_id', $exam->id)
	->where('correct', 1)
	->count();

$ntotal = $DB
	->table('question')
	->where('plant.taxonomy_id', $TAXONOMY)
	->count();


$TMPL->percent = round($ncorrect / $ntotal * 100);


echo $TMPL;
