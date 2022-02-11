const check_user_ip_checkbox = document.body.querySelector('input[id="check_user_ip_checkbox"]');

check_user_ip_checkbox.addEventListener('change', toogleCheckingIP);

document.body.querySelector('button[id="add_current_ip_to_whitelist_button"]').addEventListener('click', addCurrentIPToWhiteList);

/**
 * Enables or disables verification of the user's IP address during the authentication process via en AJAX call.
 */
function toogleCheckingIP() {

    const check_user_ip_label = document.body.querySelector('label[for="check_user_ip_checkbox"]');

    const controller_url = this.getAttribute('data-url');

    const fetch_options = {
        body: JSON.stringify(check_user_ip_checkbox.checked),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        method: 'POST'
    };

    fetch(controller_url, fetch_options)
        .then(response => response.json())
            .then(({isGuardCheckingIP}) => check_user_ip_label.textContent = isGuardCheckingIP ? "Active" : "Inactive")
                .catch(error => console.error(error));
}

/**
 * Adds the current IP to the whitelist
 */
function addCurrentIPToWhiteList() {
    
    const user_ip_addresses = document.body.querySelector('p[id="user_ip_addresses"]');
    const controller_url = this.getAttribute('data-url');

    const fetch_options = {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        method: 'GET'
    };

    fetch(controller_url, fetch_options) 
        .then(response => response.json())
                .then(({user_IP}) => {
                    if (user_ip_addresses.textContent === "") {
                        user_ip_addresses.textContent = user_IP;
                    } else {
                        if(!user_ip_addresses.textContent.includes(user_IP)) {
                            user_ip_addresses.textContent += ` | ${user_IP}`;
                        }
                    }
                })
                    .catch(error => console.error(error));
}