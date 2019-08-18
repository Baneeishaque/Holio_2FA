<?php
/* Hoiio developer credentials */
$hoiioAppId = "1QC7qC6VyNlQVCGi";
$hoiioAccessToken = "huDYhQGwisJMLMSz";
$sendSmsURL = "https://secure.hoiio.com/open/sms/send";
// User information: Query these from the db
$username = "test";
$password = "password";
$userMobileNumber = "+919446827218";
session_start();
/* print HTML for page headers */
echo <<<HEADER
<html>
    <head>
        <title>2 Factor-Authentication using SMS Example</title>
    </head>
    <body>
HEADER;
        
if($_POST == null) {
    // no form submission, show login page
    show_login_page();
} else {
    // process login page
    if(isset($_POST['login'])) {
        // Check username and password of user
        $form_username = $_POST['user'];
        $form_password = $_POST['password'];
        
        if(($form_username == $username) && ($form_password == $password)) {            
            // username and password match, generate token for 2FA
            $token = rand(100, 999);
            $message = "Your 2FA verification token is: $token";
            
            // store token in session for verification later
            $_SESSION['token'] = $token;
            
            /* send SMS containing token */
            /* prepare HTTP POST variables */
            $fields = array(
                        'app_id' => urlencode($hoiioAppId),
                        'access_token' => urlencode($hoiioAccessToken),
                        'dest' => urlencode($userMobileNumber),     // send SMS to this phone
                        'msg' => urlencode($message)                // message content in SMS
                );
            
            // form up variables in the correct format for HTTP POST
            $fields_string = "";
            foreach($fields as $key => $value) 
                $fields_string .= $key . '=' . $value . '&';
            $fields_string = rtrim($fields_string,'&');
            /* initialize cURL */
            $ch = curl_init();
            
            /* set options for cURL */
            curl_setopt($ch, CURLOPT_URL, $sendSmsURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);           
            
            /* execute HTTP POST request */
            $raw_result = curl_exec($ch);
            $result = json_decode($raw_result);     // parse JSON formatted result
            
            /* close connection */
            curl_close($ch);
            if($result->status == "success_ok") {
                // 2FA sent successfully, move on to next step
                show_2fa_page();
            } else {
                // error sending 2FA
                echo $result->status;
                show_login_page();
            }
        } else {
            // wrong username and/or password
            echo "Wrong username and/or password.";
            show_login_page();
        }
    } elseif(isset($_POST['verify'])) {     // process 2FA
        // check token
        $tokenInput = $_POST['token'];
        $token = $_SESSION['token'];
        
        if($tokenInput != $token) {
            echo "Invalid token.";
        } else { 
            echo "Login successful.";
            session_destroy();
        }
    }
}
/* print HTML for page footers */
echo <<<FOOTER
    </body>
</html>
FOOTER;
/* function to print HTML for login form */
function show_login_page() {
    echo <<<LOGIN
<h2>2 Factor-Authentication using SMS Example</h2>
<form id="login" action="" method="post">
    Use test/password to login.
    <table>
        <tr>
            <td>Username:</td>
            <td><input type="text" name="user" value=""/></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type="password" name="password" value=""/></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Login" name="login"/></td>
        </tr>
    </table>
</form>
LOGIN;
}
/* function to print HTML for 2FA form */
function show_2fa_page() {
    echo <<<TOKENVERIFY
<h2>2FA Token Verification</h2>
<form id="verify" action="" method="post">
    <table>
        <tr>
            <td colspan="2">Enter your 2FA token here:</td>
        </tr>
        <tr>
            <td>Token:</td>
            <td><input type="text" name="token" value=""/></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Submit" name="verify"/></td>
        </tr>
    </table>
</form>
TOKENVERIFY;
}
?>
