
Leaderboard = {
    gameId = nil,
    password = nil,
    url = nil,
}

--- Check the Leaderboard parameters
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

--- Send the query to the leaderboard via the CS.Web API.
-- @prama funcName (string) "Post" or "Get"
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


--- Create the game's file on the leaderboard with the combinaison of the game's id and password.
-- Set the "gameId" and "password" keys in the data table to set the game's id and password.
-- You can also set the "gameName" key to set the game's name (defaults to the game id).
-- You only ever need to call this function once. If called more than once, it will return the error "A file with the game id '[gameId]' already exists.".
-- You can also create the file directly via any browser with this url : LeaderboardURL?action=create&gameId=[gameId]&password=[password]&gamename=[gameName]
-- If the creation is successfull, the gameId and password properties are automatically set on the Leaderboard object.
-- @param data (table) The data to transmit.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.Create( data, callback )
    data = data or {}
    data.gameId = data.gameId or Leaderboard.gameId
    data.password = data.password or Leaderboard.password

    if data.gameId == nil or data.password == nil then
        error("Leaderboard.Create() : Need a gameId and password to create a game on the leaderboard.")
    else
        data.action = "create"
        Leaderboard.Query( "Get", data, function( _data, errorMsg )
            if _data and _data.success then
                Leaderboard.gameId = data.gameId
                Leaderboard.password = data.password
            end

            callback( _data, errorMsg )
        end )
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
-- Set the "name" or "score" keys to update the player's name or score.
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


--- Get the next available player id.
-- The next player id's value is under the "nextPlayerId" key in the table passed as first argument to the callback.
-- @param callback (function) The callback function to call when the request is completed.
function Leaderboard.GetNextPlayerId( callback )
    Leaderboard.Query( "Get", { action = "getnextplayerid" }, callback )
end

--- Get the full game data.
-- Player data will be sorted by score in the "playerDataByScore" table in the returned data.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.GetGameData( callback )
    Leaderboard.Query( "Get", { action = "getgamedata" }, callback )
end

-- Get a single player data.
-- @param playerId (string or number) The id of the player.
-- @param callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
function Leaderboard.GetPlayerData( playerId, callback )
    if playerId == nil then
        error("Leaderboard.GetPlayerData() : playerId argument is nil.")
    end
    Leaderboard.Query( "Get", { action = "getplayerdata", playerId = playerId }, callback )
end
