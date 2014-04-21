<?php

function GetFileContent( $filename ) {
    $files = scandir("."); // curent dir
    if (in_array($filename, $files))
        return json_decode( file_get_contents($filename), true );
    else
        return null;
}

function GetPlayerDataByScore( $gameData ) {
    $playerDataByScore = array();
    foreach ($gameData["dataByPlayerId"] as $playerId => $data) {
        if (isset($data["score"])) {
            if (!isset($data["playername"])) $data["playername"] = "Player $playerId";

            $score = $data["score"];
            if (!isset($playerDataByScore[$score]))
                $playerDataByScore[$score] = array();

            $playerDataByScore[$score][$playerId] = $data;
        }
    }
    krsort($playerDataByScore);
    return $playerDataByScore;
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
                        "gamename" => isset($_POST["gamename"]) ? $_POST["gamename"] : "".$gameid,
                        "nextPlayerId" => 0,
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
                    if (isset($_POST["gamename"]))
                        $gameData["gamename"] = $_POST["gamename"];
                    
                    if (isset($_POST["emptyPlayersData"]) && $_POST["emptyPlayersData"] == true)
                        $gameData["dataByPlayerId"] = array();

                    $returnedData["success"] = "The game name for game with id '$gameid' has been successfully updated.";
                }
                else { // datacontent == playerdata
                    $playerid = isset($_POST["playerid"]) ? $_POST["playerid"] : "";
                    if (trim($playerid) == "") {
                        $returnedData["error"] = "Wrong player id '$playerid' for game with id '$gameid'.";
                    }
                    else {
                        if (!isset($gameData["dataByPlayerId"][$playerid])) {
                            $gameData["dataByPlayerId"][$playerid] = array("name" => "Player $playerid");
                        }

                        $score = isset($_POST["score"]) ? $_POST["score"] : null;
                        if (trim($score) == "") $score = null;
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


else { // GET
    $queryString = trim( $_SERVER["QUERY_STRING"], "/" ); // GET part, after index.php?
    if ($queryString != "") {
        // url type : [gameid]/[password]/datacontent
        // or just "[gameid]" > show score table
        $chunks = explode("/", $queryString);
        
        $gameid = "";
        if (isset($chunks[0]))
            $gameid = $chunks[0];

        $password = "";
        if (isset($chunks[1]))
            $password = $chunks[1];
        
        $dataContent = "";
        if (isset($chunks[2]))
            $dataContent = strtolower($chunks[2]);
        // dataContents are : gamedata / playerdata / nextplayerid

        isset($chunks[3]) ? $playerId = $chunks[3] : $playerId = "";

        $returnedData = array();
        
        
        if ($password == "" && $dataContent == "") {
            // display scores table 
            $gameidExists = false;
            $files = scandir(".");
            $fileName = "";
            foreach ($files as $_filename) {
                $matches = array();
                preg_match("#^".$gameid."_.+\.json$#i", $_filename, $matches);
                if (isset($matches[0])) {
                    $fileName = $matches[0];
                    break;
                }
            }

            if ($fileName != "") {
                $gameData = GetFileContent($fileName);
                $playerDataByScore = GetPlayerDataByScore($gameData);
                
                include "table_template.php";
                return;
            }
            else {
                echo "No game with id '$gameid' has been found !";
                return;
            }
        }
        elseif ($password !== null && $dataContent != "") {
            $fileName = $gameid."_".$password.".json";
            $gameData = GetFileContent($fileName);
            
            if ($gameData !== null) {
                if ($dataContent == "nextplayerid") {
                    $returnedData['nextPlayerId'] = ++$gameData["nextPlayerId"];

                    file_put_contents($fileName, json_encode($gameData)); // save next player id
                }
                elseif ($dataContent == "gamedata") {
                    $gameData["playerDataByScore"] = GetPlayerDataByScore($gameData);
                    $returnedData = $gameData;
                }
                elseif ($dataContent == "playerdata") {
                    if ($playerId == "" || !isset($gameData["dataByPlayerId"][$playerId])) {
                        $returnedData["error"] = "Wrong player id '$playerId' or player id not found for game with id '$gameid'.";
                    }
                    else {
                        $returnedData = $gameData["dataByPlayerId"][$playerId];
                    }
                }
                else {
                    $returnedData["error"] = "Wrong data content '$dataContent' for game with id '$gameid'. Data content must be 'gamedata', 'playerdata' or 'nextplayerid'.";
                }
            }
            else
                $returnedData["error"] = "File not found. Wrong gameid or password with datacontent '$dataContent'.";
        }

        echo json_encode($returnedData);
        return;
    }
    else { // index
        // display explanations
        include "about.php";
    }
}