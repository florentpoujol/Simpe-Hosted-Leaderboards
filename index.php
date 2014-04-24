<?php
/*
CraftStudio Leaderboard

A simple leaderboard system in PHP that can work ("host") with multiple games.
<https://github.com/florentpoujol/CraftStudio-Leaderboard>

Copyright © 2014 Florent POUJOL, published under the WTFPL license.
<florentpoujol.fr>
*/

function GetFileContent( $fileName ) {
    $files = scandir("."); // curent dir
    if (in_array($fileName, $files))
        return json_decode( file_get_contents($fileName), true );
    else
        return null;
}

function GetPlayerDataByScore( $gameData ) {
    $playerDataByScore = array();
    foreach ($gameData["dataByPlayerId"] as $playerId => $data) {
        if (isset($data["score"])) {
            if (!isset($data["playerName"])) $data["playerName"] = "Player $playerId";

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
    $returnedData = array();
    
    $action = isset($_POST["action"]) ? $_POST["action"] : "";

    if ($action == "updategamedata" || $action == "updateplayerdata") {

        $gameId = isset($_POST["gameId"]) ? str_replace("_", " ", $_POST["gameId"]) : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $fileName = $gameId."_".$password.".json";

        if ($gameId != "" && $password != "") {
            $gameData = GetFileContent($fileName);
            
            if ($gameData === null) {
                // no file found, because no file exists yet, or bad password
             
                // check if a file with the specified gameId already exists
                $gameIdExists = false;
                $files = scandir(".");
                foreach ($files as $_fileName) {
                    if (preg_match("#^".$gameId."_.+\.json$#i", $_fileName)) {
                        $gameIdExists = true;
                        break;
                    }
                }

                // game id found but wrong password
                if ($gameIdExists) {
                    $returnedData["error"] = "Wrong password for game with id '$gameId'.";
                }
                elseif ($action == "gamedata") {
                    // no file found, create one
                    $gameData = array(
                        "gameId" => $gameId,
                        "password" => $password,
                        "gameName" => isset($_POST["gameName"]) ? $_POST["gameName"] : "".$gameId,
                        "nextPlayerId" => 0,
                        "dataByPlayerId" => array()
                    );
                    file_put_contents($fileName, json_encode($gameData));

                    $returnedData["success"] = "File for game with id '$gameId' has been successfully created.";
                }
                else {
                    // action is 'playerdata' but no file is found
                    $returnedData["error"] = "File not found for game with id '$gameId'. Create it first.";
                }
            }
            else { // file exists, good gameId and password
                if ($action == "updategamedata") {
                    // just update the game data
                    // (never update gameId or password)
                    if (isset($_POST["gameName"]))
                        $gameData["gameName"] = $_POST["gameName"];
                    
                    if (isset($_POST["emptyPlayersData"]) && $_POST["emptyPlayersData"] == true)
                        $gameData["dataByPlayerId"] = array();

                    $returnedData["success"] = "The game with id '$gameId' has been successfully updated.";
                }
                elseif ($action == "updateplayerdata") {
                    $playerId = isset($_POST["playerId"]) ? $_POST["playerId"] : "";
                    if (trim($playerId) == "") {
                        $returnedData["error"] = "Wrong player id '$playerId' for game with id '$gameId'.";
                    }
                    else {
                        if (!isset($gameData["dataByPlayerId"][$playerId])) {
                            $gameData["dataByPlayerId"][$playerId] = array("name" => "Player $playerId");
                        }

                        $score = isset($_POST["score"]) ? $_POST["score"] : null;
                        if (trim($score) == "") $score = null;
                        $gameData["dataByPlayerId"][$playerId]["score"] = $score;

                        isset($_POST["playerName"]) ? $gameData["dataByPlayerId"][$playerId]["name"] = $_POST["playerName"] : null;

                        $returnedData["success"] = "The player with id '$playerId' for game with id '$gameId' has been successfully updated.";
                    }
                }

                if (!isset($returnedData["error"])) {
                    file_put_contents($fileName, json_encode($gameData));
                }
            }
        }
        else {
            $returnedData["error"] = "No gameId or password pased with action '$action'.";
        }
    }
    else {
        $returnedData["error"] = "Wrong action '$action' for game with id '$gameId'. In a POST context, action must be either 'updategamedata' or 'updateplayerdata'.";
    }

    echo json_encode($returnedData);
    return;
} // end of POST

elseif (!empty($_GET)) {
    $returnedData = array();
    
    $action = "";
    if (isset($_GET["action"]))
        $action = strtolower($_GET["action"]);
    $actions = array("getgamedata", "getplayerdata", "getnextplayerid", "create", "viewscores", "");

    $gameId = "";
    if (isset($_GET["gameId"]))
        $gameId = $_GET["gameId"];

    if (in_array($action, $actions)) {
        $password = "";
        if (isset($_GET["password"]))
            $password = $_GET["password"];
        
        $fileName = $gameId."_".$password.".json";
        $gameData = GetFileContent($fileName);
        
        if ($gameId != "") {
            if ($action == "create") {
                // check if file does not exists yet

                if ($gameData === null) {
                    // file is not found but
                    // check if a file with the specified gameId already exists
                    $gameIdExists = false;
                    $files = scandir(".");
                    foreach ($files as $_fileName) {
                        if (preg_match("#^".$gameId."_.+\.json$#i", $_fileName)) {
                            $gameIdExists = true;
                            break;
                        }
                    }

                    // game id found but wrong password
                    if ($gameIdExists) {
                        $returnedData["error"] = "A file with the game id '$gameId' already exists.";
                    }
                    else {
                        // no file found, create one
                        $gameData = array(
                            "gameId" => $gameId,
                            "password" => $password,
                            "gameName" => isset($_GET["gameName"]) ? $_GET["gameName"] : "".$gameId,
                            "nextPlayerId" => 0,
                            "dataByPlayerId" => array()
                        );
                        file_put_contents($fileName, json_encode($gameData));

                        $returnedData["success"] = "File for game with id '$gameId' has been successfully created.";
                    }
                }
                else {
                    $returnedData["error"] = "A file with the game id '$gameId' already exists.";
                }
            }
            elseif ($action == "viewscores" || $action == "") {
                // find the file with the provided gameId
                $files = scandir(".");
                $fileName = "";
                foreach ($files as $_fileName) {
                    $matches = array();
                    preg_match("#^".$gameId."_.+\.json$#i", $_fileName, $matches);
                    if (isset($matches[0])) {
                        $fileName = $matches[0];
                        break;
                    }
                }

                if ($fileName != "") {
                    $gameData = GetFileContent($fileName);
                    $playerDataByScore = GetPlayerDataByScore($gameData);
                    
                    include "score_table.php";
                    return;
                }
                else {
                    echo "No game with id '$gameId' has been found !";
                    return;
                }
            }
            elseif ($gameData !== null) {
                if ($action == "getnextplayerid") {
                    $returnedData['nextPlayerId'] = ++$gameData["nextPlayerId"];
                    file_put_contents($fileName, json_encode($gameData)); // save next player id
                }
                elseif ($action == "getgamedata") {
                    $gameData["playerDataByScore"] = GetPlayerDataByScore($gameData);
                    $returnedData = $gameData;
                }
                elseif ($action == "getplayerdata") {
                    $playerId = "";
                    if (isset($_GET["playerId"]))
                        $playerId = strtolower($_GET["playerId"]);

                    if (!isset($gameData["dataByPlayerId"][$playerId])) {
                        $returnedData["error"] = "Player id '$playerId' not found for game with id '$gameId'.";
                    }
                    else {
                        $returnedData = $gameData["dataByPlayerId"][$playerId];
                    }
                }
            }
            else {
                $returnedData["error"] = "File not found. Wrong gameId or password with action '$action'.";
            }
        }
        else {
            $returnedData["error"] = "No gameId or password pased with action '$action'.";
        }   
    }
    else {
        $returnedData["error"] = "Wrong action '$action' for game with id '$gameId' in a GET context.";
    } 

    echo json_encode($returnedData);
    return;
} // end of GET

else { 
    // get the list of games
    $gameDataByGameId = array();
    $files = scandir(".");
    foreach ($files as $_fileName) {
        $matches = array();
        if (preg_match("#^([^_]+)_.+\.json$#i", $_fileName, $matches)) {
            $gameDataByGameId[$matches[1]] = json_decode(file_get_contents($matches[0]), true);
        }
    }

    include "about.php";
}