<?php
/**
 * Drag and Drop file uploader.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../documents.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$patient_id = filter_input(INPUT_GET, 'patient_id');
$category_id = filter_input(INPUT_GET, 'parent_id');

if (!empty($_FILES)) {
    $uploadedFile = $_FILES['file'] ?? reset($_FILES);
    $name = $uploadedFile['name'] ?? null;
    $type = $uploadedFile['type'] ?? null;
    $tmp_name = $uploadedFile['tmp_name'] ?? null;
    $error = $uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE;
    $size = $uploadedFile['size'] ?? 0;
    $owner = $GLOBALS['userauthorized'];

    if (!empty($name) && $error === UPLOAD_ERR_OK) {
        addNewDocument($name, $type, $tmp_name, $error, $size, $owner, $patient_id, $category_id);
    }
}
