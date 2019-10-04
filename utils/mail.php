<?php
$mFrom = empty($_POST['from']) ? NULL : $_POST['from'];
$mServer = empty($_POST['server']) ? NULL : $_POST['server'];
$mPassword = empty($_POST['password']) ? NULL : $_POST['password'];
$mTo = empty($_POST['to']) ? NULL : $_POST['to'];
$mSubject = empty($_POST['subject']) ? NULL : $_POST['subject'];
$mBody = empty($_POST['body']) ? NULL : $_POST['body'];
$mSend = empty($_POST['send']) ? NULL : $_POST['send'];
$errors = [];
if ($mSend && $mServer && $mPassword && $mTo) {
	$transport = (new Swift_SmtpTransport($mServer, 25))
		->setAuthMode('PLAIN')
		->setUsername($mFrom)
		->setPassword($mPassword)
		->setEncryption('tls');
	$mailer = new Swift_Mailer($transport);
	$logger = new Swift_Plugins_Loggers_ArrayLogger();
	$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

	try {
		$message = (new Swift_Message())
			->setSubject($mSubject)
			->setFrom($mFrom)
			->setTo($mTo)
			->setCharset('UTF-8')
			->setEncoder(Swift_DependencyContainer::getInstance()->lookup('mime.safeqpcontentencoder'));
	}
	catch (Swift_DependencyException $e) {
		$errors[] = $e->getMessage();
	}
	$message->setBody($mBody, 'text/plain');
	try {
		$mailer->send($message);
	}
	catch (Swift_SwiftException $e) {
		$errors[] = explode("\n", $e->getMessage())[0];
	}
}
?>
<form action="<?= URL_ROOT ?>mail" method="post">
	<label for="from">From (also account)</label>
	<input class="form-control" type="text" name="from" id="from" value="<?= htmlentities($mFrom) ?>"/>
	<label for="server">SMTP server</label>
	<input class="form-control" type="text" name="server" id="server" value="<?= htmlentities($mServer) ?>"/>
	<label for="password">SMPT password</label>
	<input class="form-control" type="password" name="password" id="password" value="<?= htmlentities($mPassword) ?>"/>
	<label for="to">To</label>
	<input class="form-control" type="text" name="to" id="to" value="<?= htmlentities($mTo) ?>"/>
	<label for="subject">Subject</label>
	<input class="form-control" type="text" name="subject" id="subject" value="<?= htmlentities($mSubject) ?>"/>
	<label for="subject">Body</label>
	<textarea class="form-control" name="body" id="body"><?= htmlentities($mBody) ?></textarea>
	<button name="send" value="send" class="btn btn-success" title="Send"><span class="fa fa-ok"></span> Send!</button>
</form>
<?php
if (!empty($errors)) { ?>
Sending message s failed with following errors:
<ul>
<?php foreach ($errors as $error) { ?>
	<li><?= $error ?></li>
<?php } ?>
</ul>
<?php }
if (!empty($logger)){
	echo '<hr><pre>'.$logger->dump().'</pre>';
}
