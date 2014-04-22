<html>
    <head>
        <title>Leaderboard for CraftStudio games</title>
    </head>
    <body>
        <?php
            require_once "lib/MarkdownInterface.php";
            require_once "lib/Markdown.php";
            echo Michelf\Markdown::defaultTransform( file_get_contents( 'md/about.md' ) );
        ?>
    </body>
</html>