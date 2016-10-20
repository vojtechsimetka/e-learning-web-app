<?php
/**
 * ITU project - e-learning system for czech plants
 * Test
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   test.php
 */

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME);

$TMPL->setFile('./latte/test.latte');

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
if (!$exam)
{// Create new exam
	$exam = $DB->table('exam')->insert(array(
		'user_id' => $USER->identity->id,
		'taxonomy_id' => $TAXONOMY,
		'level' => $LEVEL,
		'date' => new DateTime
	));
}

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
	));
}
$TMPL->answer = $answer;


if ($ACTION == 'default')
{
	
	
	$taxonomy = $DB->table('taxonomy')->get($TAXONOMY);
	$TMPL->taxonomy = $taxonomy;
	
	$TMPL->question = $question;
	$TMPL->plant = $question->plant;
	$TMPL->next = get_next($questions);
	$TMPL->prev = get_prev($questions);

	$TMPL->current = $question->order;
	$TMPL->total = end($questions)->order;

	if ($LEVEL == 1)
	{
		$limit = 5 * 60;
		
		$TMPL->options = array(
			$question->ref('option', 'option_1'),
			$question->ref('option', 'option_2'),
			$question->ref('option', 'option_3'),
			$question->ref('option', 'option_4')
		);
	}
	elseif ($LEVEL == 2)
	{
		$limit = 10 * 60;
	}
	
	$TMPL->countdown = $exam->date->getTimestamp() + $limit - time();
	echo $TMPL;
}
elseif ($ACTION == 'save')
{
	$text = $REQUEST->query['answer'];
	
	if ($LEVEL == 1)
	{// Save easy answer
		$correct = $question->plant->name == $text ? 1 : 0;
		
		$answer->update(array(
			'option_id' => $text,
			'correct' => $correct
		));
	}
	elseif ($LEVEL == 2)
	{// Save hard answer
		$name = $DB->table('option')->get($question->plant->name)->text;
		$correct = Strings::webalize($text) == Strings::webalize($name) ? 1 : 0;
		
		$answer->update(array(
			'answer' => Strings::trim($text),
			'correct' => $correct
		));
	}
}
