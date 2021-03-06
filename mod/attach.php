<?php
/**
 * @file mod/attach.php
 */
use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Database\DBM;

require_once 'include/dba.php';
require_once 'include/security.php';

function attach_init(App $a)
{
	if ($a->argc != 2) {
		notice(L10n::t('Item not available.') . EOL);
		return;
	}

	$item_id = intval($a->argv[1]);

	// Check for existence, which will also provide us the owner uid

	$r = dba::selectFirst('attach', [], ['id' => $item_id]);
	if (!DBM::is_result($r)) {
		notice(L10n::t('Item was not found.'). EOL);
		return;
	}

	$sql_extra = permissions_sql($r['uid']);

	// Now we'll see if we can access the attachment

	$r = q("SELECT * FROM `attach` WHERE `id` = '%d' $sql_extra LIMIT 1",
		dbesc($item_id)
	);

	if (!DBM::is_result($r)) {
		notice(L10n::t('Permission denied.') . EOL);
		return;
	}

	// Use quotes around the filename to prevent a "multiple Content-Disposition"
	// error in Chrome for filenames with commas in them
	header('Content-type: ' . $r[0]['filetype']);
	header('Content-length: ' . $r[0]['filesize']);
	if (isset($_GET['attachment']) && $_GET['attachment'] === '0') {
		header('Content-disposition: filename="' . $r[0]['filename'] . '"');
	} else {
		header('Content-disposition: attachment; filename="' . $r[0]['filename'] . '"');
	}

	echo $r[0]['data'];
	killme();
	// NOTREACHED
}
