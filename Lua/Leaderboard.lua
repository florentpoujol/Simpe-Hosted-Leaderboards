
Leaderboard = {
    gameId = nil,
    password = nil,
    url = nil,
}

function Leaderboard.Check( funcName )
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
        print("Leaderboard."..funcName.."() : "..msg)
        return false
    end
    return true
end

function Leaderboard.HandleCallback( error, data, userCallback )
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
    if type( userCallback ) == "function" then
        userCallback( userData, errorMsg )
    elseif errorMsg ~= nil then
        print("Leaderboard ERROR : ", errorMsg)
    end
end

-- @param callback (function) The callback function to call when the request is completed. The callback is passed with 
function Leaderboard.Query( funcName, data, callback )
    if Leaderboard.Check() then
        data.gameId = Leaderboard.gameId
        data.password = Leaderboard.password

        CS.Web[funcName]( Leaderboard.url, data, CS.Web.ResponseType.JSON, 
            function( error, data ) 
                Leaderboard.HandleCallback(error, data, callback)
            end 
        )
    end
end


function Leaderboard.UpdateGameData( data, callback )
    data.action = "updategamedata"
    Leaderboard.Query( "Post", data, callback )
end

function Leaderboard.UpdatePlayerData( playerId, data, callback )
    data.playerId = playerId
    data.action = "updateplayerdata"
    Leaderboard.Query( "Post", data, callback )
end


--- Get the next available player id 
-- Under the "nextPlayerId" key in the data table passed to the callback.
-- @param callback (function) The callback function to call when the request is completed.
function Leaderboard.GetNextPlayerId( callback )
    Leaderboard.Query( "Get", { action = "getnextplayerid" }, callback )
end

-- @param callback (function) The callback function to call when the request is completed.
function Leaderboard.GetGameData( callback )
    Leaderboard.Query( "Get", { action = "getgamedata" }, callback )
end


-- @param callback (function) The callback function to call when the request is completed.
function Leaderboard.GetPlayerData( playerId, callback )
    Leaderboard.Query( "Get", { action = "getplayerdata", playerId = playerId }, callback )
end

