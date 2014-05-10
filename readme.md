# Simple hosted leaderboards

A simple leaderboard system in PHP that can host leaderboards for multiple games.

An SDK is provided to easily work with the leaderboard from [CraftStudio](http://craftstud.io) games.

Released under the [WTFPL](http://www.wtfpl.net) licence.

- [Usage](#usage)
- [CraftStudio SDK](#craftstudio-sdk)

<a name="usage"></a>
## Usage

You interact with the script via POST/GET request. 

When the request result in any error, a JSON object is returned with the error message as value of the `error` key.  
When a POST request is successful, a JSON object is returned with the success message as value of the `success` key.  
When a GET request is successful, a JSON object is returned with the requested data.  


### Creating a game file

A leaderboard is characterized by a game id and a password which both can contain any alpha numeric character as a string.
Send a GET request with the following parameters to create your game's file :

- gameId=[id]
- password=[password]
- action=create

You can just type this in any browser's navigation bar : `[Leaderboard URL]/index.php?gameId=[id]&password=[password]&action=create`

Each game's leaderboard data is saved in a JSON file with this structure :

    {
        "gameId": "[id]",
        "password": "[password]",
        "gameName": "[name]",
        "nextPlayerId": 0,

        "dataByPlayerId": {
            "[playerId]": {
                "name": "[name]",
                "score": "[score]"
            },
            ...
        }
    }


### Setting/Updating data

The game data includes the game's name and the players data.  
Send a POST request with the following parameters to update the game's name and/or empty the players data :

- gameId=[id]
- password=[password]
- action=updategamedata
- gameName=[name]
- emptyPlayersData=true

Each player has its own unique id which can be any string (with any characters).  
Send a POST request with the following parameters to set/update one player's data :

- gameId=[id]
- password=[password]
- action=updateplayerdata
- playerId=[player id]
- name=[player name]
- score=[player score]


### Getting data

If you lack imagination for your player's id, you can get a numerical id, incremented each time.  
A GET request with the following parameters returns a JSON object with the id as the value of the `nextPlayerId` key.

- gameId=[id]
- password=[password]
- action=getnextplayerid

Send a GET request with the following parameters to get all of the game's data :

- gameId=[id]
- password=[password]
- action=getgamedata

It returns a JSON object with the whole content of the game file (as described above), plus one entry where players data are stored by score with this structure :

    "playersDataByScore": {
        "[score 1]": {
            "[player id 1]": { [player data] },
            "[player id 2]": { [player data] },
            ...
        },
        "[score 2]": {
            "[player id 3]": { [player data] },
            "[player id 4]": { [player data] },
            ...
        },
    }

Send a GET request with the following parameters to get one player data :

- gameId=[id]
- password=[password]
- action=getplayerdata
- playerId=[player id]

It returns a JSON object containing the "name" and "score" keys (Note that the score is saved as a string).


<a name="craftstudio-sdk"></a>
## CraftStudio SDK

To make things super easy in [CraftStudio](http://craftstud.io), you can use the `Leaderboard` object :

- `Leaderboard.UpdateGameData( data, callback )`
- `Leaderboard.UpdatePlayerData( playerId, data, callback )`
- `Leaderboard.GetGameData( callback )`
- `Leaderboard.GetNextPlayerId( callback )`
- `Leaderboard.GetPlayerData( playerId, callback )`

The callbacks are passed with two arguments : the returned data (table) and an eventual error message (string). Only one of the argument is set at the same time.

How to install :

- Copy and paste the [CraftStudio/Leaderboard.lua](https://raw.githubusercontent.com/florentpoujol/Simpe-Hosted-Leaderboards/master/CraftStudio/Leaderboard.lua) script in CraftStudio (you can also [find it as part of my Toolbox](http://florentpoujol.fr/craftstudio/toolbox)).
- Then set the `Leaderboard.gameId`, `Leaderboard.password` and  `Leaderboard.url` properties.

If you don't want to host the leaderboard script yourself, you can use the one I host at [http://csleaderboard.florentpoujol.fr](http://csleaderboard.florentpoujol.fr).


### Function reference

<table class="function_list">
    
        <tr>
            <td class="name"><a href="#Leaderboard.GetGameData">Leaderboard.GetGameData</a>( callback )</td>
            <td class="summary">Get the full game data.</td>
        </tr>
    
        <tr>
            <td class="name"><a href="#Leaderboard.GetNextPlayerId">Leaderboard.GetNextPlayerId</a>( callback )</td>
            <td class="summary">Get the next available player id.</td>
        </tr>
    
        <tr>
            <td class="name"><a href="#Leaderboard.GetPlayerData">Leaderboard.GetPlayerData</a>( playerId, callback )</td>
            <td class="summary">Get a single player data.</td>
        </tr>
    
        <tr>
            <td class="name"><a href="#Leaderboard.UpdateGameData">Leaderboard.UpdateGameData</a>( data, callback )</td>
            <td class="summary">Update the general game's data saved by the leaderboard.</td>
        </tr>
    
        <tr>
            <td class="name"><a href="#Leaderboard.UpdatePlayerData">Leaderboard.UpdatePlayerData</a>( playerId, data, callback )</td>
            <td class="summary">Update one player data.</td>
        </tr>
    
</table>

<dl class="function">    
        
<dt><a name="Leaderboard.GetGameData"></a><h3>Leaderboard.GetGameData( callback )</h3></dt>
<dd>
Get the full game data. Player data will be stored by score descending (big values first) in the "playerDataSortedByScoreDesc" table in the returned data.
<br><br>

    <strong>Parameters:</strong>
    <ul>
        
        <li>
          callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
        </li>
        
    </ul>


</dd>
<hr>
    
        
<dt><a name="Leaderboard.GetNextPlayerId"></a><h3>Leaderboard.GetNextPlayerId( callback )</h3></dt>
<dd>
Get the next available player id. The next player id's value is under the "nextPlayerId" key in the table passed as first argument to the callback.
<br><br>

    <strong>Parameters:</strong>
    <ul>
        
        <li>
          callback (function) The callback function to call when the request is completed.
        </li>
        
    </ul>


</dd>
<hr>
    
        
<dt><a name="Leaderboard.GetPlayerData"></a><h3>Leaderboard.GetPlayerData( playerId, callback )</h3></dt>
<dd>
Get a single player data.
<br><br>

    <strong>Parameters:</strong>
    <ul>
        
        <li>
          playerId (string or number) The id of the player.
        </li>
        
        <li>
          callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
        </li>
        
    </ul>


</dd>
<hr>
    
        
<dt><a name="Leaderboard.UpdateGameData"></a><h3>Leaderboard.UpdateGameData( data, callback )</h3></dt>
<dd>
Update the general game's data saved by the leaderboard. Set the "gameName" key to upadte the game's name. Set the "emptyPlayersData" key to true to empty the leaderboard's "dataByPlayerId" object.
<br><br>

    <strong>Parameters:</strong>
    <ul>
        
        <li>
          data (table) The data to transmit.
        </li>
        
        <li>
          callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
        </li>
        
    </ul>


</dd>
<hr>
    
        
<dt><a name="Leaderboard.UpdatePlayerData"></a><h3>Leaderboard.UpdatePlayerData( playerId, data, callback )</h3></dt>
<dd>
Update one player data. Set the "id", "name" or "score" keys to update the player's id, name (default's to "Player [playerId]") or score. The player id can be any string. You can get a unique numerical id with Leaderboard.GetNextPlayerId().
<br><br>

    <strong>Parameters:</strong>
    <ul>
        
        <li>
          playerId (string or number) The id of the player.
        </li>
        
        <li>
          data (table) [optional] The data to transmit.
        </li>
        
        <li>
          callback (function) [optional] The callback function to call when the request is completed. The callback is passed with two arguments : the returned data and an eventual error message (only one of the argument is set at the same time).
        </li>
        
    </ul>


</dd>
<hr>
    
</dl>