<?php
/**
 * ITU project - e-learning system for czech plants
 * Test show
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   show-test.php
 */

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME);

$TMPL->setFile('./latte/show-test.latte');

// Load questions from database
$questions = $DB
	->table('question')
	->where('plant.taxonomy_id', $TAXONOMY)
	->order('order')
	->fetchPairs('id');

if (isset($ID))
{// Get first question
	while (key($questions) != $ID) next($questions);
}
$question = current($questions);
$ID = $question->id;

// Load exam description
$exam = $DB
	->table('exam')
	->where('user_id', $USER->identity->id)
	->where('taxonomy_id', $TAXONOMY)
	->where('level', $LEVEL)
	->fetch();

// Load question answer
$answer = $DB
	->table('answer')
	->where('exam_id', $exam->id)
	->where('question_id', $ID)
	->fetch();
if (!$answer)
{// Create new answer
	$answer = $DB->table('answer')->insert(array(
		'exam_id' => $exam->id,
		'question_id' => $ID,
		'correct' => 0
	));
}
$TMPL->answer = $answer;

$taxonomy = $DB->table('taxonomy')->get($TAXONOMY);
$TMPL->taxonomy = $taxonomy;

$TMPL->question = $question;
$TMPL->plant = $question->plant;
$TMPL->next = get_next($questions);
$TMPL->prev = get_prev($questions);

$TMPL->current = $question->order;
$TMPL->total = end($questions)->order;


/**
 * Get option class
 * @param option Current option ID
 * @param selected Selected option ID
 * @param correct Correct option ID
 * @return Option class
 */
function option_class($option)
{
	global $question;
	global $answer;
	$correct = $question->plant->name;
	
	if (isset($answer->option_id))
	{
		if ($option == $answer->option_id && $option == $correct)
			return "test_correct_text";
		elseif ($option == $answer->option_id && $option != $correct)
			return "test_incorrect_text";
		elseif ($option == $correct)
			return "test_correct_text";
	}
	else
	{
		if ($option == $correct)
			return "test_notset_text";
	}
}

if ($LEVEL == 1)
{
	$TMPL->options = array(
		$question->ref('option', 'option_1'),
		$question->ref('option', 'option_2'),
		$question->ref('option', 'option_3'),
		$question->ref('option', 'option_4')
	);
	
	$TMPL->correct = $question->plant->name;
}
elseif ($LEVEL == 2)
{
	$TMPL->plant_name = $DB->table('option')->get($question->plant->name)->text;
}

echo $TMPL;
