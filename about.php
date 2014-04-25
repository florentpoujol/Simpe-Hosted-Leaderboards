<html>
    <head>
        <title>Simple leaderboard hosting</title>
    </head>
    <body>
        <?php
            require_once "lib/MarkdownInterface.php";
            require_once "lib/Markdown.php";
            echo Michelf\Markdown::defaultTransform( file_get_contents( 'md/about.md' ) );
        ?>

        <br>
        <p>
            Check out all the games leaderboard hosted here :
        </p>

        <?php if (count($gameDataByGameId) <= 0): ?>
            <p><em>No games yet.</em></p>
        <?php else: ?>

        <ul>
            <?php
            foreach ($gameDataByGameId as $gameId => $gameData) {
                echo '<li><a href="?gameId='.$gameData["gameId"].'">'.$gameData["gameName"].'</a></li>';
            }
            ?>
        </ul>
    <?php endif; ?>
    </body>
</html>