<?php
/**
 * ITU project - e-learning system for czech plants
 * Common file for all subsystems
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   common.php
 */

use Nette\Security;
use Nette\Database\Connection;
use Nette\Diagnostics\Debugger;
use Nette\Templating\FileTemplate;

require_once('./libs/nette.php');

$HOME = 'http://www.planta.cz/';

/**
 * User authenticator class
 */
class UserAuthenticator extends Nette\Object implements Security\IAuthenticator
{
	public $db;
	
	function __construct(Connection $db)
	{
		$this->db = $db;
	}
	
	function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		
		$user = $this->db
			->table('user')
			->where('login', $username)
			->fetch();
		
		if (!$user)
		{// Register new user
			$user = $this->db->table('user')->insert(array(
				'login' => $username,
				'password' => md5($password)
			));
		}
		else
		{
			if ($user->password !== md5($password))
			{// Invalid credentials
				throw new Security\AuthenticationException('Neplatné přihlašovací údaje.');
			}
		}
		return new Security\Identity($user->id);
	}
}


/**
 * Add additional query to URL
 * @param url Original URL
 * @param query Query array to add
 */
function add_query($url, $query)
{
	$url = new Nette\Http\UrlScript($url);
	$old_query = array();
	$add_query = array();
	parse_str($url->query, $old_query);
	parse_str($query, $add_query);
	$url->query = array_merge($old_query, $add_query);
	return $url;
}


/**
 * Redirect
 * @param url
 * @param query
 */
function redirect($url, $query = '')
{
	global $RESPONSE;
	$RESPONSE->redirect(add_query($url, $query));
	exit;
}


/**
 * Compute percent color
 * @param percent
 * @return CSS color definition 
 */
function percent_color($percent)
{
	$green = round($percent / 100 * 255);
	$red = 255 - $green;
	return 'rgb(' . $red . ',' . $green . ',0)'; 
}


/**
 * Get looped previous element in array
 * @param array
 * @return Element
 */
function get_prev($array)
{
	return prev($array) ? current($array) : end($array);
}

/**
 * Get looped next element in array
 * @param array
 * @return Element
 */
function get_next($array)
{
	return next($array) ? current($array) : reset($array);
}

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
// $configurator->setDebugMode();
$configurator->enableDebugger('./log');

// Create Dependency Injection container
$configurator->setTempDirectory('./tmp');

$configurator->addConfig('./config.neon');
$container = $configurator->createContainer();

// Database
$DB = $container->database;

$REQUEST = $container->httpRequest;
$RESPONSE = $container->httpResponse;

// User information
$USER = $container->user;
$USER->getStorage()->setNamespace('planta');

// Create template
$TMPL = new FileTemplate;
$TMPL->registerHelperLoader('Nette\Templating\Helpers::loader');
$TMPL->setCacheStorage(new Nette\Caching\Storages\PhpFileStorage('./tmp/cache'));
$TMPL->onPrepareFilters[] = function($template) {
	$template->registerFilter(new Nette\Latte\Engine);
};
$TMPL->user = $USER;
$TMPL->confirm = array();
$TMPL->db = $DB;
$TMPL->request = $REQUEST;
$TMPL->home = $HOME;

if ($USER->identity != NULL)
{// Load user profile
	$row = $DB->table('user')->get($USER->identity->id);
	
	$USER->identity->login = $row->login;
}

// Decode requested action
if (isset($REQUEST->query['action']) && !empty($REQUEST->query['action']))
	$ACTION = $REQUEST->query['action'];
else
	$ACTION = 'default';
$TMPL->action = $ACTION;

// Decode next action
if (isset($REQUEST->query['naction']) && !empty($REQUEST->query['naction']))
	$NACTION = $REQUEST->query['naction'];
else
	$NACTION = "default";
$TMPL->naction = $NACTION;

// Decode status
if (isset($REQUEST->query['status']) && !empty($REQUEST->query['status']))
	$STATUS = $REQUEST->query['status'];
else
	$STATUS = 'default';
$TMPL->status = $STATUS;

// Decode ID
if (isset($REQUEST->query['id']) && !empty($REQUEST->query['id']))
{
	$ID = $REQUEST->query['id'];
	$TMPL->id = $ID;
}

// Decode taxonomy ID
if (isset($REQUEST->query['taxonomy']) && !empty($REQUEST->query['taxonomy']))
{
	$TAXONOMY = $REQUEST->query['taxonomy'];
	$TMPL->taxonomy = $TAXONOMY;
}

// Decode test level
if (isset($REQUEST->query['level']) && !empty($REQUEST->query['level']))
{
	$LEVEL = $REQUEST->query['level'];
	$TMPL->level = $LEVEL;
}
