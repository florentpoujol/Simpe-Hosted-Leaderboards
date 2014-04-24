-- Leaderboard.lua
--
-- Wrapper for CraftStudio to work with the "CraftStudio Leaderboard" PHP script.
-- <https://github.com/florentpoujol/CraftStudio-Leaderboard>
-- 
-- Copyright © 2014 Florent POUJOL, published under the WTFPL license.
-- <florentpoujol.fr>


Leaderboard = {
    gameId = nil,
    password = nil,
    url = "http://csleaderboard.florentpoujol.fr/index.php",
}


-- Check the Leaderboard parameters.
-- @return (boolean) False if some parameter is not set, True otherwise.
function Leaderboard.Check()
    local msg = ""
    if Leaderboard.url == nil then
        msg = "Leaderboard.url is not set. "
    end
    if Leaderboard.gameId == nil then
        msg = msg.."Leaderboard.gameId is not set. "
    end
    if Leaderboard.password == nil then
        msg = msg.."Leaderboard.password is not set. "
    end
    if msg ~= "" then
        print("Leaderboard.Check() : "..msg)
        return false
    end
    return true
end

-- Send the query to the leaderboard via the CS.Web API.
-- @prama funcName (string) "Post" or "Get".
-- @param data (table) [optional] The data to transmit.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.Query( funcName, data, callback )
    if Leaderboard.Check() then
        data.gameId = Leaderboard.gameId
        data.password = Leaderboard.password

        CS.Web[funcName]( Leaderboard.url, data, CS.Web.ResponseType.JSON, 
            function( error, data ) 
                local errorMsg = nil
                local userData = nil
                if error ~= nil then
                    errorMsg = error.message
                elseif data ~= nil then
                    if data.error ~= nil then
                        errorMsg = data.error
                    else
                        userData = data
                    end
                end
                if type( callback ) == "function" then
                    callback( userData, errorMsg )
                elseif errorMsg ~= nil then
                    print("Leaderboard ERROR : ", errorMsg)
                end
            end 
        )
    end
end


--- Update the general game's data saved by the leaderboard.
-- Set the "gameName" key to upadte the game's name.
-- Set the "emptyPlayersData" key to true to empty the leaderboard's "dataByPlayerId" object.
-- @param data (table) The data to transmit.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.UpdateGameData( data, callback )
    data = data or {}
    data.action = "updategamedata"
    Leaderboard.Query( "Post", data, callback )
end

--- Update one player data.
-- Set the "id", "name" or "score" keys to update the player's id, name (default's to "Player [playerId]") or score.
-- The player id can be any string. You can get a unique numerical id with Leaderboard.GetNextPlayerId().
-- @param playerId (string or number) The id of the player.
-- @param data (table) [optional] The data to transmit.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.UpdatePlayerData( playerId, data, callback )
    if playerId == nil then
        error("Leaderboard.UpdatePlayerData() : playerId argument is nil.")
    end
    data = data or {} 
    data.playerId = playerId
    data.action = "updateplayerdata"
    Leaderboard.Query( "Post", data, callback )
end



--- Get the full game data.
-- Player data will be stored by score in the "playerDataByScore" table in the returned data.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.GetGameData( callback )
    Leaderboard.Query( "Get", { action = "getgamedata" }, callback )
end

--- Get a single player data.
-- @param playerId (string or number) The id of the player.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.GetPlayerData( playerId, callback )
    if playerId == nil then
        error("Leaderboard.GetPlayerData() : playerId argument is nil.")
    end
    Leaderboard.Query( "Get", { action = "getplayerdata", playerId = playerId }, callback )
end

--- Get the next available player id.
-- The next player id's value is under the "nextPlayerId" key in the table passed as first argument to the callback.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.GetNextPlayerId( callback )
    Leaderboard.Query( "Get", { action = "getnextplayerid" }, callback )
end
