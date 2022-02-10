const check_user_ip_checkbox = document.body.querySelector('input[id="check_user_ip_checkbox"]');

check_user_ip_checkbox.addEventListener('change', toogleCheckingIP);

document.body.querySelector('button[id="add_current_ip_to_whitelist_button"]').addEventListener('click', addCurrentIPToWhiteList);

/**
 * Enables or disables verification of the user's IP address during the authentication process via en AJAX call.
 */
function toogleCheckingIP() {

}

/**
 * Adds the current IP to the whitelist
 */
function addCurrentIPToWhiteList() {
    
}