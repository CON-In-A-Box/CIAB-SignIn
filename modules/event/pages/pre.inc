<?php

/*.
    require_module 'standard';
    require_module 'json';
.*/

if (!isset($_SESSION['IS_ADMIN'])) {
    // This is for SystemAdmins only!  Silently return to main.
    goSite();
}

require_once(__DIR__.'/../functions/functions.inc');

if (!empty($_REQUEST)) {
    $arguments = [
    'reloadFromNeon' => FILTER_SANITIZE_NUMBER_INT,
    ];

    $updateData = filter_input_array(INPUT_GET, $arguments);

    if (isset($updateData['reloadFromNeon'])) {
        import_events();
        import_meetings();
        header("Refresh:0; url=index.php?Function=event");
        exit();
    }

    /* POST TIME */

    $arguments = [
    'modify' => FILTER_SANITIZE_STRING,
    'delete' => FILTER_SANITIZE_NUMBER,
    'cycle' => FILTER_SANITIZE_STRING,
    ];

    $updateData = filter_input_array(INPUT_POST, $arguments);

    if (isset($updateData['modify'])) {
        $data = json_decode(base64_decode($updateData['modify']));
        save_meeting($data);
        exit();
    }

    if (isset($updateData['delete'])) {
        delete_meeting($updateData['delete']);
        exit();
    }

    if (isset($updateData['cycle'])) {
        $data = json_decode(base64_decode($updateData['cycle']));
        new_cycle($data);
        exit();
    }
}