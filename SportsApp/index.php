<?php
    switch($_GET['url']){
        case '':
            echo "<h1>This is Sports API</h1>";
            break;
        case 'api/login':
            require 'api/Login.php';
            break;
        case 'api/register':
            require 'api/Register.php';
            break;
        case 'api/users/me':
            require 'api/User.php';
            break;
        case 'api/creatematch':
            require 'api/creatematch.php';
            break;
        case 'api/setplaying11':
            require 'api/setplaying11.php';
            break;
        case 'api/getmatches':
            require 'api/getmatches.php';
            break;
        case 'api/getmatch':
            require 'api/getmatch.php';
            break;
        case 'api/getplaying11':
            require 'api/getplaying11.php';
            break;
        case 'api/getusers':
            require 'api/getusers.php';
            break;
        case 'api/getscores':
            require 'api/getscores.php';
            break;
        case 'api/updatepassword':
            require 'api/updatepassword.php';
            break;
        case 'api/getcurrentplayers':
            require 'api/getcurrentplayers.php';
            break;
        case 'api/settoss':
            require 'api/settoss.php';
            break;
        case 'api/gettoss':
            require 'api/gettoss.php';
            break;
        case 'api/getbattingplayers':
            require 'api/getbattingplayers.php';
            break;
        case 'api/getbowlingplayers':
            require 'api/getbowlingplayers.php';
            break;
        case 'api/setcurrentplayers':
            require 'api/setcurrentplayers.php';
            break;
        case 'api/updatescore':
            require 'api/updatescore.php';
            break;
        case 'api/strikechange':
            require 'api/strikechange.php';
            break;
        case 'api/getcurrentscores':
            require 'api/getcurrentscores.php';
            break;
        case 'api/wideball':
            require 'api/wideball.php';
            break;
        case 'api/changebowler':
            require 'api/changebowler.php';
            break;
        case 'api/getmatchbowlers':
            require 'api/getmatchbowlers.php';
            break;
        case 'api/recordwicket':
            require 'api/recordwicket.php';
            break;
        case 'api/getnotoutbatsmen':
            require 'api/getnotoutbatsmen.php';
            break;
        case 'api/finishinning':
            require 'api/finishinning.php';
            break;
        default:
            header("HTTP/1.0 404 Not Found");
            echo json_encode(["message" => "Endpoint not found"]);
            break;
    }
?>
