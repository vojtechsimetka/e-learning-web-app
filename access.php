<?php
/**
 * ITU project - e-learning system for czech plants
 * Login form
 * 
 * @author Hynek Blaha     xblaha22@stud.fit.vutbr.cz
 *         Jiri Hon        xhonji01@stud.fit.vutbr.cz
 *         Vojtech Simetka xsimet00@stud.fit.vutbr.cz
 * @date   2012/12/03
 * @file   access.php
 */

use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;

require_once './common.php';

// Set template file
$TMPL->setFile('./latte/access.latte');

$form = new Form;
$form->setAction('./access.php');

$form->addText('login', 'Login', NULL, 60)
     ->setAttribute('placeholder', 'Jméno')
     ->setRequired('Vyplňte prosím jméno.');
$form->addPassword('password', 'Heslo')
     ->setAttribute('placeholder', 'Heslo')
     ->setRequired('Vyplňte prosím heslo.');

$form->addSubmit('send', 'Přihlásit se');

if ($ACTION == 'logout')
{// Logout user
	$USER->logout();
	$TMPL->confirm[] = 'Uživatel úspěšně odhlášen';
}

if (!is_null($USER->identity))
{// Prefill user name and permanent check
	$form->setDefaults(array(
		'login'    => $USER->identity->login
	));
}

if ($form->isSuccess())
{// Form submitted and data valid
	try
	{// Try to log user in
		$values = $form->getValues();
		$USER->login($values['login'], $values['password']);
		
		$container->user->setExpiration(0, TRUE);
		
		redirect($HOME);
	}
	catch (Nette\Security\AuthenticationException $e)
	{// Invalid credentials
		$form->addError($e->getMessage());
	}
}

$TMPL->form = $form;
echo $TMPL;
