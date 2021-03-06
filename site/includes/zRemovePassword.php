<?php
include 'dbh.inc.php';
require_once 'passwordFunctions.php';
require_once 'functions.php';
session_start();

CheckLoggedIn($conn, true);


//checks if the page is loaded correctly
if (isset($_GET['ChatroomID'])) {


    $UserID = $_SESSION['userID'];
    $ChatroomID = mysqli_real_escape_string($conn, $_GET['ChatroomID']);

    //checks if the input  and Chatroomid was not emtpy
    if ($ChatroomID != "") {

        //statement to get the admin connector between this user and the Chatroom the new user is being added to
        $sqlVerifyChatroomConnector =
            "SELECT
                connector.ID
            FROM
                connector
            WHERE
                connector.UserID = '$UserID' AND 
                connector.Admin = '1' AND
                connector.ChatroomID = '$ChatroomID';";

        //checks if there was a connector found
        if (mysqli_num_rows(mysqli_query($conn, $sqlVerifyChatroomConnector)) > 0) {


            //query to get all messages from this chat
            $sqlGetMessages =
                "SELECT
                    message.ID AS 'messageID',
                    message.Content AS 'messageContent'
                FROM
                    message
                WHERE 
                    message.ChatroomID = '$ChatroomID';";

            //calls the query
            $AllMessagesResult = mysqli_query($conn, $sqlGetMessages);

            //checks if there were any results
            if (mysqli_num_rows($AllMessagesResult) > 0) {

                //gets the next row of data returned by the query
                while ($messageRow = mysqli_fetch_assoc($AllMessagesResult)) {

                    $decryptionKey = $_SESSION['ChatroomID_' . $ChatroomID];
                    $encryptedMessage = $messageRow['messageContent'];
                    $messageID = $messageRow['messageID'];

                    $decryptedMessage = DecryptString($encryptedMessage, $decryptionKey);

                    $sqlSaveDecryptedMessage =
                        "UPDATE
                            message
                        SET
                            message.Content = '$decryptedMessage'
                        WHERE
                            message.ID = '$messageID';";
                    mysqli_query($conn, $sqlSaveDecryptedMessage);
                }
            }

            //query to reset the password
            $sqlRemovePassword =
                "UPDATE
                    chatroom
                SET
                    chatroom.PassHash = ''
                WHERE
                    chatroom.ID = $ChatroomID;";

            //calls the query
            mysqli_query($conn, $sqlRemovePassword);

            unset($_SESSION['ChatroomID_' . $ChatroomID]);

            header("Location: ../chatSettings.php?ChatroomID=" . $ChatroomID);
            exit();
        } else {
            header("Location: ../index.php");
            exit();
        }
    } else {
        header("Location: ../chatSettings.php?ChatroomID=" . $ChatroomID . "note=BadFileAccess");
        exit();
    }
} else {
    header("Location: ../chatSettings.php?ChatroomID=" . $ChatroomID . "note=BadFileAccess");
    exit();
}
