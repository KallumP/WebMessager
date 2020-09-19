<?php
include 'includes/dbh.inc.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>KPMessenger</title>
    <link href="style.css" rel="stylesheet" />
</head>

<body>
    <header>

        <?php include("includes/accountBanner.inc.php"); ?>

        <div class="Actions">
            <a href="index.php">Back to messages</a>
            <a href="searchFriends.php">Friends</a>
        </div>

    </header>

    <div class="SearchContainer">

        <h1>You can search for new friends here!</h1>
        <form action="searchAllUsers.php" method="POST">
            <?php include("includes/search.inc.php"); ?>
        </form>

        <div class="SearchResults">
            <?php

            //checks if the user has searched something
            if (isset($_POST['searchSubmit'])) {

                //gets the user search input
                $searchInput = mysqli_real_escape_string($conn, $_POST['search']);

                //checks if the user id and the username was the same as the input search 
                if ($searchInput != $_SESSION['userID'] && $searchInput != $_SESSION['userName']) {

                    //pulls the users that were searched for
                    $sqlUserSearch =
                        "SELECT
                            _user.UserName as 'userName',
                            _user.ID as 'userID'
                        FROM
                            _user
                        WHERE
                            _user.UserName = '$searchInput' OR _user.ID = '$searchInput';";

                    $UserSearchResult = mysqli_query($conn, $sqlUserSearch);
                    $UserSearchResultCheck = mysqli_num_rows($UserSearchResult);

                    //checks to see if any users were pulled
                    if ($UserSearchResultCheck > 0) {

                        //loops through each searched user
                        while ($UserSearchResultRow = mysqli_fetch_assoc($UserSearchResult)) {


                            echo "<div class='FriendBox'>";
                            echo "<h2> Username: " . $UserSearchResultRow['userName'] . "# " . $UserSearchResultRow['userID'] . "</h2>";
                            echo "<a href=includes/zSendFriendRequest.php?recipientID=" . $UserSearchResultRow['userID'] . ">Send friend request</a>";
                            echo "<a href=includes/zChatroomCreate.php?recipientID=" . $UserSearchResultRow['userID'] . ">Create new chat</a><br>";

                            //saves the current searched user id
                            $currentSearchedUserID = $UserSearchResultRow['userID'];

                            //query to pull all chatrooms that the current searched user is a part of
                            $sqlChatCheck =
                                "SELECT     
                                    chatroom.ID as 'chatID'
                                FROM
                                    chatRoom
                                LEFT JOIN connector ON chatRoom.ID = connector.ChatRoomID
                                WHERE   
                                    connector.ID = '$currentSearchedUserID';";

                            $ChatResult = mysqli_query($conn, $sqlChatCheck);
                            $ChatResultCheck = mysqli_num_rows($ChatResult);

                            //checks if there were any chats that the searched user was a part of
                            if ($ChatResultCheck > 0) {

                                while ($ChatResultRow = mysqli_fetch_assoc($ChatResult)) {

                                    //saves the users's id
                                    $userID = $_SESSION['userID'];

                                    //saves the current chat id of the current searched user
                                    $currentSearchedUserCurrentChatID = $ChatResultRow['chatID'];

                                    //query to pull all chatrooms that the current searched user is a part of that the current searched user is also a part of
                                    $sqlCommonChatCheck =
                                        "SELECT     
                                            chatroom.ID as 'chatID',
                                            chatroom.Name as 'chatName'
                                        FROM
                                            chatroom
                                        LEFT JOIN connector ON chatRoom.ID = connector.ChatRoomID
                                        WHERE   
                                            connector.ID = '$userID' AND chatroom.ID = $currentSearchedUserCurrentChatID;";

                                    $CommonChatResult = mysqli_query($conn, $sqlCommonChatCheck);
                                    $CommonChatResultCheck = mysqli_num_rows($CommonChatResult);

                                    //checks if there were any common chats
                                    if ($ChatResultCheck > 0) {

                                        //loops through each common chat
                                        while ($ChatResultRow = mysqli_fetch_assoc($ChatResult)) {

                                            echo "<a href=index.php?chatID=" . $ChatResultRow['chatID'] . ">Join chat: " . $ChatResultRow['chatName'] . "</a><br>";
                                        }
                                    }
                                }
                            }


                            echo "</div>";
                        }
                    }
                }
            }
            ?>
        </div>
    </div>

</body>