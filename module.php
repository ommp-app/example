<?php
/**
 * Online Module Management Platform
 * 
 * Main file for example module
 * Contains the required function to allow the module to work
 * 
 * @author  The OMMP Team
 * @version 1.0
 */

/**
 * Check a configuration value
 * 
 * @param string $name
 *      The configuration name (without the module name)
 * @param string $value
 *      The configuration value
 * @param Lang $lang
 *         The Lang object for the current module
 * 
 * @return boolean|string
 *      TRUE is the value is correct for the given name
 *      else a string explaination of the error
 */
function example_check_config($name, $value, $lang) {
    if ($name == "allow_negative") {
        if ($value !== "0" && $value !== "1") {
            return $lang->get('value_0_or_1');
        }
        return TRUE;
    }
}

/**
 * Handle user deletion calls
 * This function will be called by the plateform when a user is deleted,
 * it must delete all the data relative to the user
 * 
 * @param int $id
 *         The id of the user that will be deleted
 */
function example_delete_user($id) {
    // This module does not interracts with the user
}

/**
 * Handle an API call
 * 
 * @param string $action
 *      The name of the action to process
 * @param array $data
 *      The data given with the action
 * 
 * @return array|boolean
 *      An array containing the data to respond
 *      FALSE if the action does not exists
 */
function example_process_api($action, $data) {
    global $sql, $db_prefix, $user, $config;
    
    // Handle the different actions

    if ($action == "more" || $action == "less") {

        // Check the rights
        if (!$user->has_right("example.allow_edit")) {
            return ["error" => $user->module_lang->get("cannot_use_button")];
        }

        // Get the current value
        $value = intval(dbGetFirstLineSimple("{$db_prefix}example_counter", "TRUE", "value", TRUE));

        // Check if negative is forbidden
        if ($config->get("example.allow_negative") == "0" && $action == "less" && $value <= 0) {
            return ["error" => $user->module_lang->get("cannot_go_negative")];
        }

        // Update the counter
        $result = $sql->exec("UPDATE {$db_prefix}example_counter SET `value` = `value` " . ($action == "more" ? "+" : "-") . " 1");

        // Check for errors
        if ($result === FALSE || $result === 0) {
            return ["error" => $user->module_lang->get("cannot_update_value")];
        }

        // Return success and new value
        return [
            "ok" => TRUE,
            "value" => $value + ($action == "more" ? 1 : -1)
        ];

    } else if ($action == "edit") {

        // Check the parameters
        if (!check_keys($data, ["value"])) {
            return ["error" => $user->module_lang->get("missing_parameter")];
        }

        // Check the rights
        if (!$user->has_right("example.allow_direct_edit")) {
            return ["error" => $user->module_lang->get("cannot_edit")];
        }
        
        // Prepare value
        $value = intval($data['value']);

        // Check if negative is forbidden
        if ($config->get("example.allow_negative") == "0" && $value < 0) {
            return ["error" => $user->module_lang->get("cannot_go_negative")];
        }

        // Update the counter
        $result = $sql->exec("UPDATE {$db_prefix}example_counter SET `value` = $value"); // We don't escape $value because we used intval

        // Check for errors
        if ($result === FALSE || $result === 0) {
            return ["error" => $user->module_lang->get("cannot_update_value")];
        }

        // Return success and new value
        return [
            "ok" => TRUE,
            "value" => $value
        ];

    }

    return FALSE;
}

/**
 * Handle page loading for the module
 * 
 * @param string $page
 *      The page requested in the module
 * @param string $pages_path
 *      The absolute path where the pages are stored for this module
 * 
 * @return array|boolean
 *      An array containing multiple informations about the page as described below
 *      [
 *          "content" => The content of the page,
 *          "title" => The title of the page,
 *          "og_image" => The Open Graph image (optional),
 *          "description" => A description of the web page
 *      ]
 *      FALSE to generate a 404 error
 */
function example_process_page($page, $pages_path) {
    global $user, $db_prefix;

    // This module has only one page
    if ($page != "") {
        return FALSE;
    }

    // Return the page to display
    return [
        "content" => page_read_module($pages_path . "index.html", [
            "value" => intval(dbGetFirstLineSimple("{$db_prefix}example_counter", "TRUE", "value", TRUE)),
            "allow_edit" => $user->has_right("example.allow_edit") ? "1" : "0",
            "allow_direct_edit" => $user->has_right("example.allow_direct_edit") ? "1" : "0"
        ]),
        "title" => $user->module_lang->get("counter")
    ];
}

/**
 * Handle the special URL pages
 * 
 * @param string $url
 *      The url to check for a special page
 * 
 * @return boolean
 *      TRUE if this module can process this url (in this case this function will manage the whole page display)
 *      FALSE else (in this case, we will check the url with the remaining modules, order is defined by module's priority value)
 */
function example_url_handler($url) {
    // This module does not have special URL
    return FALSE;
}