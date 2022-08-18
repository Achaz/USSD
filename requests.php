<?php
/* D. light USSD Application
 * Author : James Tugume
 * Email: jtugume123@gmail.com
 * USSD gateway that is being used is Airtel USSD gateway
*/

// Print the response as plain text so that the gateway can read it
header('Content-type: text/plain');

// Get the parameters provided by Airtel's USSD gateway
$phone = $_GET['phoneNumber'];
$session_id = $_GET['sessionId'];
$serviceCode = $_GET['serviceCode'];
$ussd_string = $_GET['text'];
logs($ussd_string);
$level = checkSessions($phone, $session_id);
//Explode the text to get the value of the latest interaction - think 1*1
$textArray = explode('*', $ussd_string);
$ussd_string_exploded = trim(end($textArray));
logs($ussd_string_exploded);
//echo $ussd_string_exploded;
if (isset($phone) && isset($session_id) && isset($serviceCode) && isset($ussd_string))
{

    $account_num = check_registration($phone);
    $level = checkSessions($phone, $session_id);

    if ($level == "" && $ussd_string_exploded == "239")
    {

        home_menu();
        $level = "language_menu";
        sessions($phone, $session_id, $serviceCode, $level);

    }
    else if ($level == "language_menu" && $ussd_string_exploded == "1")
    {

        $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

        // Check connection
        if ($link === false)
        {
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        $sql = ("SELECT * FROM ussd_users WHERE phone_number ='$phone'");

        if ($result = mysqli_query($link, $sql))
        {

            //$num_rows = mysql_num_rows($result);
            if (mysqli_num_rows($result) > 0)
            {

                registered_menu();
                $level = "registered_menu";
                updateSessions($session_id, $level);

            }
            else
            {

                register_menu();
                $level = "register_menu";
                updateSessions($session_id, $level);

            }

        }
        else
        {

            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
        // Close connection
        mysqli_close($link);

    }
    else if ($level == "language_menu" && $ussd_string_exploded == "2")
    {

        luganda_menu();
        $level = "luganda";
        sessions($phone, $session_id, $serviceCode, $level);

    }
    else if ($level == "language_menu" && $ussd_string_exploded == "3")
    {

        kiswahili_menu();
        $level = "kiswahili";
        sessions($phone, $session_id, $serviceCode, $level);

    }
    else if ($level == "registered_menu" && $ussd_string_exploded == "1")
    {

        $level = "tokens";
        tokens($ussd_string, $level, $session_id);
        updateSessions($session_id, $level);

    }
    else if ($level == 'tokens' && $ussd_string_exploded == '1')
    {

        $level = "view_tokens";
        $ussd_text = "Enter Account Number:";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == "view_tokens" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $level = "view_tokens";
            $ussd_text = "Please Enter Account Number:";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            //$url = 'https://3.19.212.163:9443/CSPortalToV2/stg/mtech/ussd/lasttokens';
            $url = 'https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/lasttokens';
            $account_number = $ussd_string;

            $ch = curl_init($url);

            $data = array(
                'account_number' => $account_number,
                'MSISDN' => $phone
            );

            $payload = json_encode($data);

            // Attach encoded JSON string to the POST fields
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set the content type to application/json
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json'
            ));

            // Return response instead of outputting
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the POST request
            $result = curl_exec($ch);

            //$response = curl_exec( $ch );
            $json_resp = json_decode($result, true);
            $status = $json_resp['status'];

            if ($status == "OK")
            {

                $token = $json_resp['tokens']['token'];
                $ussd_text = "Token:" . $token;
                $level = "view_tokens";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

            if ($status == "FAILED")
            {

                $description = $json_resp['description'];
                $ussd_text = $description;
                $level = "view_tokens";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

        }

    }
    else if ($level == 'tokens' && $ussd_string_exploded == '2')
    {

        $level = "view_tv_tokens";
        $ussd_text = "Enter Account number:";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == 'view_tv_tokens' && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $level = "view_tv_tokens";
            $ussd_text = "Please Enter Account number:";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            //$url = 'https://3.19.212.163:9443/CSPortalToV2/stg/mtech/ussd/lasttokens';
            $url = 'https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/lasttokens';
            $account_number = $ussd_string_exploded;

            $ch = curl_init($url);

            $data = array(
                'account_number' => $account_number,
                'MSISDN' => $phone
            );

            $payload = json_encode($data);

            // Attach encoded JSON string to the POST fields
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set the content type to application/json
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json'
            ));

            // Return response instead of outputting
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the POST request
            $result = curl_exec($ch);

            $json_resp = json_decode($result, true);
            $status = $json_resp['status'];

            if ($status == "OK")
            {

                $token = $json_resp['tokens']['token'];
                $ussd_text = "Token:" . $token;
                $level = "tv_tokens";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

            if ($status == "FAILED")
            {

                $description = $json_resp['description'];
                $ussd_text = $description;
                $level = "tv_tokens";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

        }

    }
    else if ($level == "registered_menu" && $ussd_string_exploded == "2")
    {

        $level = "accounts_reg";
        accounts($session_id, $level);
        updateSessions($session_id, $level);

    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "1")
    {
        $ussd_text = "Please Enter Account Number";
        ussd_proceed($ussd_text);
        $level = "view_balance";
        updateSessions($session_id, $level);
    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "2")
    {
        $ussd_text = "Please enter your account number";
        $level = "token_exp";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);
    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "3")
    {
        $ussd_text = "Please enter token number;";
        $level = "add_token";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);
    }
    else if ($level == "add_token" && $ussd_string_exploded !== '')
    {
        if (empty($ussd_string))
        {
            $ussd_text = "Please enter token number;";
            $level = "add_token";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $token_rx = $ussd_string;

            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            // Check connection
            if ($link === false)
            {
                die("ERROR: Could not connect. " . mysqli_connect_error());
            }

            $sql = "INSERT INTO tokens " . "(token_rx,phone_number) " . "VALUES " . "('$token_rx','$phone')";

            if (mysqli_query($link, $sql))
            {
                //echo "Records inserted successfully.";
                $ussd_text = "Token successfully added!";
                ussd_stop($ussd_text);
                $level = "token_rx_end";
                updateSessions(session_id, $level);

            }
            else
            {
                //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

            }
            // Close connection
            mysqli_close($link);
        }

    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "4")
    {
        $ussd_text = "Please enter account number";
        $level = "new_account";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);
    }
    else if ($level == "new_account" && $ussd_string_exploded !== '')
    {
        if (empty($ussd_string))
        {
            $ussd_text = "Please enter account number";
            $level = "new_account";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            $account = $ussd_string;

            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            // Check connection
            if ($link === false)
            {
                die("ERROR: Could not connect. " . mysqli_connect_error());
            }

            $sql = "INSERT INTO accounts " . "(account_number,phone_number) " . "VALUES " . "('$account','$phone')";

            if (mysqli_query($link, $sql))
            {
                //echo "Records inserted successfully.";
                $ussd_text = "Account successfully added!";
                ussd_stop($ussd_text);
                $level = "account_end";
                updateSessions(session_id, $level);

            }
            else
            {
                //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

            }
            // Close connection
            mysqli_close($link);
        }

    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "5")
    {
        $ussd_text = "Please add TV number";
        $level = "tv_account";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == "tv_account" && $ussd_string_exploded !== '')
    {
        if (empty($ussd_string))
        {
            $ussd_text = "Please add TV number";
            $level = "tv_account";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $tv_account = $ussd_string;

            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            // Check connection
            if ($link === false)
            {
                die("ERROR: Could not connect. " . mysqli_connect_error());
            }

            $sql = "INSERT INTO tv_accounts " . "(tv_account,phone_number) " . "VALUES " . "('$tv_account','$phone')";

            if (mysqli_query($link, $sql))
            {
                //echo "Records inserted successfully.";
                $ussd_text = "TV Account successfully added!";
                ussd_stop($ussd_text);
                $level = "tv_account_end";
                updateSessions(session_id, $level);
            }
            else
            {
                //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

            }
            // Close connection
            mysqli_close($link);
        }

    }
    else if ($level == "accounts_reg" && $ussd_string_exploded == "6")
    {

        $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

        // Check connection
        if ($link === false)
        {
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        $sql = ("SELECT * FROM customer_kyc WHERE msisdn ='$phone'");

        if ($result = mysqli_query($link, $sql))
        {

            //$num_rows = mysql_num_rows($result);
            if (mysqli_num_rows($result) > 0)
            {

                while ($row = $result->fetch_assoc())
                {

                    $firstname = $row['fname'];
                    $lastname = $row['lname'];
                    $national_id = $row['national_id'];
                    $serial_number = $row['serial_num'];
                    $product_name = $row['pname'];
                    $dealer_id = $row['dealer_id'];
                    $county = $row['county'];
                    $phone = $row['msisdn'];
                    $ussd_text = "Your account is registered for Warranty below are you details;\n";
                    $ussd_text .= "\n";
                    $ussd_text .= "Firstname: " . $firstname . "\n";
                    $ussd_text .= "Lastname: " . $lastname . "\n";
                    $ussd_text .= "National_id: " . $national_id . "\n";
                    $ussd_text .= "Serial number: " . $serial_number . "\n";
                    $ussd_text .= "Product number: " . $product_name . "\n";
                    $ussd_text .= "Dealer id: " . $dealer_id . "\n";
                    $ussd_text .= "County: " . $county . "\n";
                    $level = "warranty_end";
                    ussd_stop($ussd_text);
                    updateSessions($session_id, $level);

                }

            }
            else
            {

                $ussd_string = "You haven't registered for warranty yet. Please press 1 to register";
                $level = "warranty_registration";
                ussd_proceed($ussd_string);
                updateSessions($session_id, $level);

            }

        }
        else
        {

            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
        // Close connection
        mysqli_close($link);

    }
    else if ($level == "warranty_registration" && $ussd_string_exploded == "1")
    {
        $ussd_text = "Please enter your first name.";
        ussd_proceed($ussd_text);
        $level = "firstname";
        updateSessions($session_id, $level);

        //insert the first record in the database
        register_wrt($first_name, $last_name, $national_id, $phone, $product_name, $serial_number, $county, $dealer_id);
    }
    else if ($level == "registered_menu" && $ussd_string_exploded == "3")
    {

        $level = "repairs";
        repair_menu($session_id, $level);

    }
    else if ($level == "repairs" && $ussd_string_exploded == "1")
    {

        $ussd_text = "please enter nature of your request";
        $level = "repair_request";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == "repair_request" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "please enter nature of your request";
            $level = "repair_request";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $repair_request = $ussd_string;
            $level = "repairs_end";
            $ussd_text = "Thank you. Request received. Our agents will be in touch";
            ussd_stop($ussd_text);
            repairs($repair_request, $phone);
            updateSessions($session_id, $level);
        }

    }
    else if ($level == "repairs" && $ussd_string_exploded == "2")
    {

        $ussd_text = "Please be patient abit, our service center locations will be published here.";
        ussd_stop($ussd_text);
        $level = "sc_locations";
        updateSessions($session_id, $level);

    }
    else if ($level == "register_menu" && $ussd_string_exploded == "1")
    {

        $level = "verify_id";
        $ussd_text = "Please enter National ID";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);
        register_ussd($reg_id, $account_information, $phone);

    }
    else if ($level == "verify_id" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $level = "verify_id";
            $ussd_text = "Please enter National ID";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
            register_ussd($reg_id, $account_information, $phone);
        }
        else
        {
            // code...
            $reg_id = $ussd_string;

            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  ussd_users " . "SET national_id='$reg_id' where phone_number='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $level = "account_information";
                $ussd_text = "Please enter account number";
                ussd_proceed($ussd_text);
                updateSessions($session_id, $level);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "account_information" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $level = "account_information";
            $ussd_text = "Please enter account number";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $account_details = $ussd_string;

            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  ussd_users " . "SET account_num='$account_details' where phone_number='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Thanks for registering for USSD. Please dial *239# To start using the USSD App.";
                $level = "ussd_reg_end";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }
            else
            {

            }

            mysqli_close($link);

        }

    }
    else if ($level == "register_menu" && $ussd_string_exploded == "2")
    {

        $level = "view_account";
        view_account($session_id, $level);

    }
    else if ($level == "view_account" && $ussd_string_exploded == "1")
    {

        $ussd_text = "Please enter your account number";
        $level = "view_balance";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == "view_balance" && $ussd_string_exploded !== "")
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter your account number";
            $level = "view_balance";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            $acc_number = $ussd_string;
            //$url = 'https://3.19.212.163:9443/CSPortalToV2/stg/mtech/ussd/accountinformation';
            $url = 'https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/accountinformation';
            //$account_number = $ussd_string_exploded;
            $ch = curl_init($url);

            $data = array(
                'account_number' => $acc_number,
                'MSISDN' => $phone
            );

            $payload = json_encode($data);

            // Attach encoded JSON string to the POST fields
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set the content type to application/json
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json'
            ));

            // Return response instead of outputting
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the POST request
            $result = curl_exec($ch);

            $json_resp = json_decode($result, true);
            $status = $json_resp['status'];

            //echo "\n".$json_resp;
            if ($status == "OK")
            {

                $total_paid = $json_resp['account_information']['total_paid'];
                $remaining_due = $json_resp['account_information']['remaining_due'];
                $token_expiration = $json_resp['account_information']['token_expiration'];
                $ussd_text = "Total Paid:" . $total_paid . "\n";
                $ussd_text .= "Remaining Due:" . $remaining_due . "\n";
                //$ussd_text .="Token Expiration:".$taken_expiration;
                $level = "account_balance";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);
            }

            if ($status == "FAILED")
            {

                $description = $json_resp['description'];
                $ussd_text = $description;
                $level = "account_balance";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

        }

    }
    else if ($level == "view_account" && $ussd_string_exploded == "2")
    {

        $ussd_text = "Please enter your account number";
        $level = "token_exp";
        ussd_proceed($ussd_text);
        updateSessions($session_id, $level);

    }
    else if ($level == "token_exp" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter your account number";
            $level = "token_exp";
            ussd_proceed($ussd_text);
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $acc_num = $ussd_string;
            $url = 'https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/accountinformation';
            #$url = 'https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/lasttokens';
            //$account_number = $ussd_string_exploded;
            $ch = curl_init($url);

            $data = array(
                'account_number' => $acc_num,
                'MSISDN' => $phone
            );

            $payload = json_encode($data);

            // Attach encoded JSON string to the POST fields
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set the content type to application/json
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json'
            ));

            // Return response instead of outputting
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the POST request
            $result = curl_exec($ch);

            $json_resp = json_decode($result, true);
            $status = $json_resp['status'];

            //echo "\n".$json_resp;
            if ($status == "OK")
            {

                $total_paid = $json_resp['account_information']['total_paid'];
                $remaining_due = $json_resp['account_information']['remaining_due'];
                $token_expiration = $json_resp['account_information']['token_expiration'];
                $ussd_text .= "Token Expiration:" . $token_expiration;
                $level = "token_exp_end";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);
            }

            if ($status == "FAILED")
            {

                $description = $json_resp['description'];
                $ussd_text = $description;
                $level = "token_exp_end";
                ussd_stop($ussd_text);
                updateSessions($session_id, $level);

            }

        }

    }
    else if ($level == "register_menu" && $ussd_string_exploded == "3")
    {

        $ussd_text = "Please enter your first name.";
        ussd_proceed($ussd_text);
        $level = "firstname";
        updateSessions($session_id, $level);

        //insert the first record in the database
        register_wrt($first_name, $last_name, $national_id, $phone, $product_name, $serial_number, $county, $dealer_id);

    }
    else if ($level == "firstname" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {

            $ussd_text = "Please enter your first name.";
            ussd_proceed($ussd_text);
            $level = "firstname";
            updateSessions($session_id, $level);
            //insert the first record in the database
            register_wrt($first_name, $last_name, $national_id, $phone, $product_name, $serial_number, $county, $dealer_id);

        }
        else
        {
            // code...
            $firstname = $ussd_string;
            //insert the first name here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET fname='$firstname' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter your last name.";
                ussd_proceed($ussd_text);
                $level = "lastname";
                updateSessions($session_id, $level);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "lastname" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter your last name.";
            ussd_proceed($ussd_text);
            $level = "lastname";
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $lastname = $ussd_string;
            //insert the last name here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';
            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET lname='$lastname' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter Product name.";
                ussd_proceed($ussd_text);
                $level = "productname";
                updateSessions($session_id, $level);
            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "productname" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter Product name.";
            ussd_proceed($ussd_text);
            $level = "productname";
            updateSessions($session_id, $level);
        }
        else
        {
            $product_name = $ussd_string;
            //insert the product name here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET pname='$product_name' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter serial number.";
                ussd_proceed($ussd_text);
                $level = "serial_number";
                updateSessions($session_id, $level);
            }
            else
            {

            }
            mysqli_close($link);

        }

    }
    else if ($level == "serial_number" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter serial number.";
            ussd_proceed($ussd_text);
            $level = "serial_number";
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $serial_number = $ussd_string;
            //insert serial number here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET serial_num='$serial_number' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter National ID Number";
                ussd_proceed($ussd_text);
                $level = "national_id";
                updateSessions($session_id, $level);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "national_id" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter National ID Number";
            ussd_proceed($ussd_text);
            $level = "national_id";
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $national_id = $ussd_string;
            //insert national ID here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET national_id='$national_id' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter county";
                ussd_proceed($ussd_text);
                $level = "county";
                updateSessions($session_id, $level);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "county" && $ussd_string_exploded !== '')
    {
        if (empty($ussd_string))
        {
            $ussd_text = "Please enter county";
            ussd_proceed($ussd_text);
            $level = "county";
            updateSessions($session_id, $level);
        }
        else
        {
            // code...
            $county = $ussd_string;
            //insert dealer id here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET county='$county' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $ussd_text = "Please enter Dealer ID Number";
                ussd_proceed($ussd_text);
                $level = "dealer_id";
                updateSessions($session_id, $level);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }
    else if ($level == "dealer_id" && $ussd_string_exploded !== '')
    {

        if (empty($ussd_string))
        {
            $ussd_text = "Please enter Dealer ID Number";
            ussd_proceed($ussd_text);
            $level = "dealer_id";
            updateSessions($session_id, $level);
        }
        else
        {
            $dealer_id = $ussd_string;
            //insert dealer id here
            $dbhost = 'localhost';
            $dbuser = 'ussd';
            $dbpass = 'ussd123!';
            $dbname = 'ussd';

            $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

            $sql = "UPDATE  customer_kyc " . "SET dealer_id='$dealer_id' where msisdn='$phone'";
            log($sql);
            if (mysqli_query($link, $sql))
            {
                //echo "Record was updated successfully.";
                $level = "reg_end";
                customer_kyc($level, $session_id);

            }
            else
            {

            }
            mysqli_close($link);
        }

    }

}

function check_registration($phone)
{
    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    // Attempt select query execution
    $sql = "SELECT * FROM ussd_users WHERE phone_number ='$phone'";
    if ($result = mysqli_query($link, $sql))
    {

        if (mysqli_num_rows($result) > 0)
        {

            while ($res = mysqli_fetch_array($result))
            {

                $account_number = $res['account_num'];
            }

            return $account_number;
            // Close result set
            //mysqli_free_result($result);

        }
        else
        {
            //echo "No records matching your query were found.";
            return false;
        }

    }
    else
    {

        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }

    // Close connection
    mysqli_close($link);

}

function logs($msg)
{

    $logfile = 'logs/log_' . date('d-M-Y') . '.log';
    file_put_contents($logfile, $msg . "\n", FILE_APPEND);
}

/* The ussd_proceed function appends CON to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session is till in session or should still continue
 * Use this when you want the application USSD session to continue
*/
function ussd_proceed($ussd_text)
{
    header('HTTP/1.1 200 OK');
    header('Server: Apache-Coyote/1.1');
    header('Path=/application_uri');
    header('Freeflow: FC');
    header('charge: Y');
    header('amount: 100');
    header('Expires: -1');
    header('Pragma: no-cache');
    header('Cache-Control: max-age=0');
    header('Content-Type: UTF-8');

    echo "$ussd_text";
}

/* This ussd_stop function appends END to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session should end.
 * Use this when you to want the application session to terminate/end the application
*/
function ussd_stop($ussd_text)
{
    header('HTTP/1.1 200 OK');
    header('Server: Apache-Coyote/1.1');
    header('Path=/application_uri');
    header('Freeflow: FB');
    header('charge: Y');
    header('amount: 100');
    header('Expires: -1');
    header('Pragma: no-cache');
    header('Cache-Control: max-age=0');
    header('Content-Type: UTF-8');

    echo "$ussd_text";
}

function luganda_menu()
{
    $ussd_text = "Dear Customer, the luganda menu will be coming soon.";
    ussd_stop($ussd_text);
}

function register_menu()
{
    $ussd_text = "Welcome to the D.light Solar. Please choose an option;\n";
    $ussd_text .= "\n";
    $ussd_text .= "1.Register USSD\n2.View Account\n3.Register warrant";
    ussd_proceed($ussd_text);
}

function registered_menu()
{
    $ussd_text = "Welcome back to D.light Solar. Please choose and option";
    $ussd_text .= "\n";
    $ussd_text .= "1.Tokens\n2.My account\n3.Repair service";
    ussd_proceed($ussd_text);
}

function kiswahili_menu()
{
    $ussd_text = "Mpendwa Mteja, menyu ya Kiswahili itakuja hivi karibuni.";
    ussd_stop($ussd_text);
}

function home_menu()
{
    $ussd_text = "Welcome to d.light solar,";
    $ussd_text .= " please select a language;\n";
    $ussd_text .= "\n";
    $ussd_text .= "1.English\n2.Luganda.";
    ussd_proceed($ussd_text);

}
//This is the home menu function
function display_menu()
{
    $ussd_text = "Welcome to the d.light solar English menu.";
    $ussd_text .= " please select a service below;\n";
    $ussd_text .= "\n";
    $ussd_text .= "1.Tokens\n2.Register warrant\n3.My account "; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);

}
// Function that hanldles About menu
function tokens($level, $session_id)
{
    $ussd_text = "Dear customer, welcome to the tokens menu.\n";
    $ussd_text .= "Please select type of token to view;\n";
    $ussd_text .= "\n";
    $ussd_text .= "1.View Token\n2.View TV Token";
    ussd_proceed($ussd_text);
}

function account($ussd_string)
{
    $ussd_text = "1. balance";
    ussd_proceed($ussd_text);

}
function accounts($sessions_id, $level)
{
    $ussd_text = "Welcome to the accounts menu.";
    $ussd_text .= " Please select option;\n";
    $ussd_text .= "\n";
    $ussd_text .= "1. Balance\n2. Token Expiry\n3. Add Token rx\n4. Add Account\n5. Add TV account\n6. Warranty";
    updateSessions($session_id, $level);
    ussd_proceed($ussd_text);
}

function repair_menu($session_id, $level)
{
    $ussd_text = "1.Repair request\n2.Service Center location";
    updateSessions($session_id, $level);
    ussd_proceed($ussd_text);
}

function view_account($session_id, $level)
{

    $ussd_text = "Welcome to the accounts menu.Please choose an option below;";
    $ussd_text .= "\n";
    $ussd_text .= "1.Balance\n2.Token expiry";
    updateSessions($session_id, $level);
    ussd_proceed($ussd_text);

}

function register_ussd($reg_id, $account_information, $phone)
{

    $dbhost = 'localhost';
    $dbuser = 'ussd';
    $dbpass = 'ussd123!';
    $dbname = 'ussd';

    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "INSERT INTO ussd_users " . "(national_id,account_num,phone_number) " . "VALUES " . "('$reg_id','$account_information','$phone')";

    if (mysqli_query($link, $sql))
    {
        //echo "Records inserted successfully.";

    }
    else
    {
        //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

    }
    // Close connection
    mysqli_close($link);

}

function repairs($requests, $phone)
{

    $dbhost = 'localhost';
    $dbuser = 'ussd';
    $dbpass = 'ussd123!';
    $dbname = 'ussd';

    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "INSERT INTO repairs " . "(request,phone_num) " . "VALUES " . "('$requests','$phone')";

    if (mysqli_query($link, $sql))
    {
        //echo "Records inserted successfully.";

    }
    else
    {
        //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

    }
    // Close connection
    mysqli_close($link);

}

function register_wrt($first_name, $last_name, $national_id, $msisdn, $product_name, $serial_number, $county, $dealer_id)
{

    $dbhost = 'localhost';
    $dbuser = 'ussd';
    $dbpass = 'ussd123!';
    $dbname = 'ussd';

    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "INSERT INTO customer_kyc " . "(fname,lname,national_id,msisdn,pname,serial_num,county,dealer_id) " . "VALUES " . "('$first_name','$last_name','$national_id','$msisdn','$product_name','$serial_number','$county','$dealer_id')";

    if (mysqli_query($link, $sql))
    {
        //echo "Records inserted successfully.";

    }
    else
    {
        //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

    }
    // Close connection
    mysqli_close($link);

}

function customer_kyc($level, $session_id)
{

    updateSessions($session_id, $level);

    //register_wrt($first_name,$last_name,$national_id,$phone,$product_name,$serial_number,$dealer_id);
    //fetch details to post here
    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    // Attempt select query execution
    $sql = "SELECT * FROM customer_kyc WHERE msisdn ='$phone'";
    if ($result = mysqli_query($link, $sql))
    {

        if (mysqli_num_rows($result) > 0)
        {

            while ($res = mysqli_fetch_array($result))
            {

                $level = $res['level'];
                $firstname = $res['fname'];
                $lastname = $res['lname'];
                $national_id = $res['national_id'];
                $serial_number = $res['serial_num'];
                $product_name = $res['pname'];
                $county = $res['county'];
                $dealer_id = $res['dealer_id'];
                $phone = $res['msisdn'];

                //$url = 'https://3.19.212.163:9443/CSPortalToV2/stg/mtech/ussd/warranty';
                $url = "https://coregateway.staging.dlight.com/coreserviceussdapi/mtnuganda/ussd/warranty";

                $ch = curl_init($url);

                $data = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'national_id' => $national_id,
                    'MSISDN' => $phone,
                    'product_name' => $product_name,
                    'county' => $county,
                    'serial_number' => $serial_number,
                    'dealer_id' => $dealer_id
                );

                $payload = json_encode($data);
                // Attach encoded JSON string to the POST fields
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                // Set the content type to application/json
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type:application/json'
                ));
                // Return response instead of outputting
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // Execute the POST request
                $result = curl_exec($ch);
                //$response = curl_exec( $ch );
                $json_resp = json_decode($result, true);

                $status = $json_resp['status'];

                if ($status == "OK")
                {

                    $description = $json_resp['description'];
                    $ussd_text = $description;
                    $level = "end_registration";
                    ussd_stop($ussd_text);
                    updateSessions($session_id, $level);

                }

                if ($status == "FAILED")
                {

                    $description = $json_resp['description'];
                    $ussd_text = $description;
                    $level = "end_registration";
                    ussd_stop($ussd_text);
                    updateSessions($session_id, $level);

                }

            }

            return $level;
            // Close result set
            //mysqli_free_result($result);

        }
        else
        {
            //echo "No records matching your query were found.";
            return false;
        }

    }
    else
    {

        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }

    // Close connection
    mysqli_close($link);

}

function sessions($phone, $session_id, $serviceCode, $level)
{

    $dbhost = 'localhost';
    $dbuser = 'ussd';
    $dbpass = 'ussd123!';
    $dbname = 'ussd';

    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "INSERT INTO sessions " . "(msisdn,serviceCode,sessionId,level) " . "VALUES " . "('$phone','$serviceCode','$session_id','$level')";

    if (mysqli_query($link, $sql))
    {
        //echo "Records inserted successfully.";

    }
    else
    {
        //echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

    }

    // Close connection
    mysqli_close($link);
}

function checkSessions($phone, $session_id)
{
    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    // Check connection
    if ($link === false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    // Attempt select query execution
    $sql = "SELECT * FROM sessions WHERE msisdn ='$phone'and sessionId ='$session_id'";
    if ($result = mysqli_query($link, $sql))
    {

        if (mysqli_num_rows($result) > 0)
        {

            while ($res = mysqli_fetch_array($result))
            {

                $level = $res['level'];
            }

            return $level;
            // Close result set
            //mysqli_free_result($result);

        }
        else
        {
            //echo "No records matching your query were found.";
            return false;
        }

    }
    else
    {

        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }

    // Close connection
    mysqli_close($link);

}

function updateSessions($session_id, $level)
{
    $dbhost = 'localhost';
    $dbuser = 'ussd';
    $dbpass = 'ussd123!';
    $dbname = 'ussd';

    $link = mysqli_connect("localhost", "ussd", "ussd123!", "ussd");

    $sql = "UPDATE  sessions " . "SET level='$level' where sessionId='$session_id'";
    log($sql);
    if (mysqli_query($link, $sql))
    {
        //echo "Record was updated successfully.";

    }
    else
    {

    }
    mysqli_close($link);

}

?>
