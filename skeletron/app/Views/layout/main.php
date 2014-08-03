<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Layout</title>
    <link rel="stylesheet" href="<?= Rec::$url ?>public/css/grid.css"/>
    <link rel="stylesheet" href="<?= Rec::$url ?>public/css/styles.css"/>

    <script type="application/javascript" src="<?= Rec::$url ?>public/js/jquery-1.8.3.min.js"></script>

    <link rel="stylesheet" href="<?= Rec::$url ?>public/highlight/styles/obsidian.css">
    <script src="<?= Rec::$url ?>public/highlight/highlight.pack.js"></script>

    <script type="application/javascript">
        hljs.initHighlightingOnLoad();
    </script>

</head>
<body>

<div class="top-menu full">
    <div class="page full menu">
        <a href="<?= Rec::$url ?>main">Rec framework</a>
        <a href="<?= Rec::$url ?>docs">Documentation</a>
        <a href="<?= Rec::$url ?>download">Download</a>
        <?php if (!$this->auth): ?>
            <a href="<?= Rec::$url ?>login">Login</a>
        <?php else: ?>
            <a href="<?= Rec::$url ?>logout">Logout</a>
        <?php endif; ?>
    </div>
</div>

<div class="page full clear">

    <?php $this->layout('layout'); ?>

    <div class="footer">
        Copyright © - 2014 SunLight, Inc. OL Werdffelynir. All rights reserved. <br>
        Was compiled per: <?php echo round(microtime(true) - START_TIMER, 4); ?> sec.
    </div>

</div>



</body>
</html>