<html>
    <head>
        <title><?php echo $gameData["gamename"] ?> LeaderBoard</title>

    </head>

    <body>
        <h1> <?php echo $gameData["gamename"] ?> LeaderBoard </h1>

        <table>
            <tr>
                <th>Name</th>
                <th>score</th>
            </tr>

            <?php
            foreach ($playerDataByScore as $score => $playersData) {
                foreach ($playersData as $playerId => $data) {
            ?>
            <tr>
                <td><?php echo $data["name"] ?></td>
                <td><?php echo $data["score"] ?></td>
            </tr>
            <?php
                }
            }
            ?>

        </table>
    </body>
</html>