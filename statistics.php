<?php
/**
 * ITU project - e-learning system for czech plants
 * Test statistics
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/07
 * @file   statistics.php
 */

use Nette\Diagnostics\Debugger;

require_once './common.php';

if (!$USER->isLoggedIn())
// Redirect user to login page
	redirect($HOME . 'access.php');

$TMPL->setFile('./latte/statistics.latte');

// Load user exams
$exams = $DB
	->table('exam')
	->where('user_id', $USER->identity->id);

$results = array();

foreach ($exams as $exam)
{
	$ncorrect = $DB
		->table('answer')
		->where('exam_id', $exam->id)
		->where('correct', 1)
		->count();
	$ntotal = $DB
		->table('question')
		->where('plant.taxonomy_id', $exam->taxonomy_id)
		->count();
	$results[] = round($ncorrect / $ntotal * 100);
}

$TMPL->avrg_percent = round(array_sum($results) / count($results));

if ($ACTION == 'detail')
{// Generate histogram
	$scores = array();
	
	foreach ($DB->table('user') as $user)
	{
		$results = array();
		
		foreach ($DB->table('exam')->where('user_id', $user->id) as $exam)
		{
			$ncorrect = $DB
				->table('answer')
				->where('exam_id', $exam->id)
				->where('correct', 1)
				->count();
			$ntotal = $DB
				->table('question')
				->where('plant.taxonomy_id', $exam->taxonomy_id)
				->count();
			$results[] = round($ncorrect / $ntotal * 100);
		}
		
		if (!empty($results))
			$scores[] = round(array_sum($results) / count($results));
	}
	$histogram = array_fill(0, 10, 0);

	foreach ($scores as $score)
	{// Sort out scores to histogram
		if ($score < 10)
			$histogram[0]++;
		elseif ($score < 20)
			$histogram[1]++;
		elseif ($score < 30)
			$histogram[2]++;
		elseif ($score < 40)
			$histogram[3]++;
		elseif ($score < 50)
			$histogram[4]++;
		elseif ($score < 60)
			$histogram[5]++;
		elseif ($score < 70)
			$histogram[6]++;
		elseif ($score < 80)
			$histogram[7]++;
		elseif ($score < 90)
			$histogram[8]++;
		else
			$histogram[9]++;
	}
	$count = count($scores);
	
	$zoom = 350 / max($histogram);
	
	foreach ($histogram as $key => $value)
	{// Normalize histogram to width 350
		$histogram[$key] = round($value * $zoom);
	}
	$TMPL->histogram = $histogram;
}

echo $TMPL;
