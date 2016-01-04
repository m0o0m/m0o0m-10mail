<?php

/** 
 * GentleSource News Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', '../');
define('T12L_ALTERNATIVE_TEMPLATE', 'admin');
define('T12L_LOGIN_LEVEL', 1);

$t12l_detail_template    = 'backup.tpl.html';

// -----------------------------------------------------------------------------




// Include
require T12L_ROOT . 'include/core.inc.php';

// Start output handling
$out = new t12l_output($t12l_detail_template);

// -----------------------------------------------------------------------------




require 'backup.class.inc.php';
$backup = new t12l_backup;


// Export database into file
if (t12l_gpc_vars('do') == 'ex') {
    if (false == $t12l['demo_mode']) {
        if ($backup->export()) {
            header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . dirname($_SERVER['PHP_SELF']) . '/backup.php?e=s');
            exit;
        }
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}
if (t12l_gpc_vars('e') == 's') {
    $t12l['message'][] = $t12l['text']['txt_export_successful'];
}

// -----------------------------------------------------------------------------




// Delete backup file
if ($file = t12l_gpc_vars('f')
        and t12l_gpc_vars('do') == 'de') {
    $delete_confirmation = array(
                            'dialogue'      => 1,
                            'file' => $file
                            );
    $out->assign('delete_confirmation', $delete_confirmation);
}
if (t12l_gpc_vars('f')
        and t12l_gpc_vars('do') == 'dec') {   
    if (false == $t12l['demo_mode']) {
        if ($backup->delete($file)) {
            $t12l['message'][] = $t12l['text']['txt_delete_file_successful'];
        } else {        
            $t12l['message'][] = $t12l['text']['txt_delete_file_failed'];
        }
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------




// Import backup file
if ($file = t12l_gpc_vars('f')
        and t12l_gpc_vars('do') == 'im') {
    $import_confirmation = array(
                            'dialogue'      => 1,
                            'file'          => $file
                            );
    $out->assign('import_confirmation', $import_confirmation);
}
if (t12l_gpc_vars('f')
        and t12l_gpc_vars('do') == 'imc') {   
    if (false == $t12l['demo_mode']) {
        if ($backup->import($file)) {
            header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . dirname($_SERVER['PHP_SELF']) . '/backup.php?i=s');
            exit;
        } else {        
            $t12l['message'][] = $t12l['text']['txt_import_failed'];
        }
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}
if (t12l_gpc_vars('i') == 's') {
    $t12l['message'][] = $t12l['text']['txt_import_successful'];
}

// -----------------------------------------------------------------------------




// Download backup file
if ($file = t12l_gpc_vars('f')
        and t12l_gpc_vars('do') == 'dl') {
    require_once 'download.class.inc.php';
    if (is_file(T12L_ROOT . $t12l['backup_directory'] . $file)){
        t12l_download::send(T12L_ROOT . $t12l['backup_directory'] . $file);
    }
}

// -----------------------------------------------------------------------------




// Manually remove /backup/.htaccess and /backup/ (safe mode, uid etc.)
if (t12l_gpc_vars('t12l_dbf')) {
    $delete_backup_folder = true;
    if (!is_dir(T12L_ROOT . $t12l['backup_directory'])) {
        $delete_backup_folder = false;
    }
    if ($delete_backup_folder and is_file(T12L_ROOT . $t12l['backup_directory'] . '.htaccess')) {
        unlink(T12L_ROOT . $t12l['backup_directory'] . '.htaccess');
    }
    if ($delete_backup_folder) {
        rmdir(T12L_ROOT . $t12l['backup_directory']);
    }
}

// -----------------------------------------------------------------------------




// List available backup files
$out->assign('backup_files', $backup->file_list());

// -----------------------------------------------------------------------------




// Output
$out->finish();






?>
