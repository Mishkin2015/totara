<?php  //$Id$

// This file keeps track of upgrades to 
// the course_list block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_guides_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }
    if ($result && $oldversion < 2010070400) {
        /// Define key 'name' (unique) to be added to guides
        $table = new XMLDBTable('block_guides_guide');
        $key = new XMLDBKey('name');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('name'));
        $result = $result && add_key($table, $key);
    }
    if ($result && $oldversion < 2010073000) {
        // Add a new 'identifier' field, and move the unique constraint from name to identifier
        $table = new XMLDBTable('block_guides_guide');
        $field = new XMLDBField('identifier');
        $field->setAttributes(XMLDB_TYPE_CHAR, '50', null, null, null, null, null);
        $result = $result && add_field($table, $field);

        $guideindexname = $CFG->prefix . 'blocguidguid_nam_uix';
        if ($table->getIndex($guideindexname)) {
            $result = $result && $table->deleteIndex($guideindexname);
        }
        $key = new XMLDBKey('identifier');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('identifier'));
        $result = $result && add_key($table, $key);
    }

    return $result;
}

?>