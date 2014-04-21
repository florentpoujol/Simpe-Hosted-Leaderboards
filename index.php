<?php

function GetFileContent( $filename ) {
    $files = scandir("."); // curent dir
    if (in_array($filename, $files))
        return json_decode( file_get_contents($filename), true );
    else
        return null;
}


if (!empty($_POST)) {

    $datacontent = isset($_POST["datacontent"]) ? $_POST["datacontent"] : "";

    if ($datacontent == "gamedata" || $datacontent == "playerdata") {

        $gameid = isset($_POST["gameid"]) ? $_POST["gameid"] : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $filename = $gameid."_".$password.".json";

        if ($gameid != "" && $password != "") {
            $gameData = GetFileContent($filename);
            
            if ($gameData === null) {
                // no file yet, or bad password
             
                // check if a file with the specified gameid already exists
                $gameidExists = false;
                $files = scandir(".");
                foreach ($files as $_filename) {
                    if (preg_match("#^".$gameid."_.+\.json$#i", $_filename)) {
                        $gameidExists = true;
                        break;
                    }
                }

                // game id found but wrong password
                if ($gameidExists) {
                    $returnedData["error"] = "Wrong password for game with id '$gameid'.";
                }
                elseif ($datacontent == "gamedata") {
                    // no file found, create one
                    $gameData = array(
                        "gameid" => $gameid,
                        "password" => $password,
                        "gamename" => isset($_POST["gamename"]) ? $_POST["gamename"] : "",
                        "dataByPlayerId" => array()
                    );
                    file_put_contents($filename, json_encode($gameData));

                    $returnedData["success"] = "File for game with id '$gameid' has been successfully created.";
                }
                else {
                    // datacontent is 'playerdata' but no file is found
                    $returnedData["error"] = "File not found for game with id '$gameid'. Create it first.";
                }
            }
            else { // file exists, good gameid and password
                if ($datacontent == "gamedata") {
                    // just update the game data
                    // (never update gameid or password)
                    if (isset($_POST["gamename"])) {
                        $gameData["gamename"] = $_POST["gamename"];
                        $returnedData["success"] = "The game name for game with id '$gameid' has been successfully updated.";
                    }
                }
                else { // datacontent == playerdata
                    $playerid = isset($_POST["playerid"]) ? $_POST["playerid"] : "";
                    if (trim($playerid) == "") {
                        $returnedData["error"] = "Wrong player id '$playerid' for game with id '$gameid'.";
                    }
                    else {
                        if (!isset($gameData["dataByPlayerId"][$playerid])) {
                            $gameData["dataByPlayerId"][$playerid] = array(
                                "id" => $playerid,
                                "name" => "Player $playerid",
                            );
                        }

                        $score = isset($_POST["score"]) ? $_POST["score"] : null;
                        $gameData["dataByPlayerId"][$playerid]["score"] = $score;

                        isset($_POST["playername"]) ? $gameData["dataByPlayerId"][$playerid]["name"] = $_POST["playername"] : null;

                        $returnedData["success"] = "The player with id '$playerid' for game with id '$gameid' has been successfully updated.";
                    }
                }

                if (!isset($returnedData["error"])) {
                    file_put_contents($filename, json_encode($gameData));
                    
                }
            }
        }
        else {
            $returnedData["error"] = "No gameid or password with datacontent '$datacontent'.";
        }
    }
    else {
        $returnedData["error"] = "Wrong datacontent '$datacontent' in a POST context. Data content must be either 'gamedata' or 'playerdata'.";
    }

    echo json_encode($returnedData);
    return;
} // end of POST
