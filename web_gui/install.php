<?php
/**
 * Installation helper.
 */
$composer = __DIR__ . '/../vendor';
$config = __DIR__ . '/../config.php';
$xhprof = __DIR__ . '/../xhprof/xhprof_lib';
$req = array(
    'xhprof' => array(
        'title' => 'XHProf Core',
        'status' => (file_exists($xhprof)),
        'content' => "Run 'git submodule init && git submodule update'"
    ),
    'composer' => array(
        'title' => 'Composer',
        'status' => (file_exists($composer) && is_dir($composer) && is_readable($composer)),
        'content' => 'Please run <strong>composer install</strong> from the project root.',
    ),
    'installer' => array(
        'title' => 'Install script',
        'status' => false,
        'content' => 'Be sure to remove "install.php"'
    ),
    'config' => array(
        'title' => 'Configuration',
        'status' => (file_exists(__DIR__ . '/../config.ini')),
        'content' => "Copy 'config.ini.example' to 'config.ini' and edit the values"
    )
                     
);
$composer = (file_exists('../vendor') && is_dir('../vendor'));
$composer_readable = (is_readable('../vendor'));

$requirements = $req;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>XHProf Graph Installer</title>
        <link rel="stylesheet" href="css/uikit.min.css">
    </head>
    <body>
        <div class="uk-container uk-container-center">
            <h1>Install</h1>

            <div class="uk-grid">
                <?php foreach ($requirements as $name => $panel): ?>
                    <div class="uk-panel uk-width-1-2 uk-panel-box uk-panel-hover">
                        <div class="uk-panel-heading">
                            <h2 class="uk-panel-title"><?=$panel['title'];?></h2>
                        </div>
                        <?php if ($panel['status']): ?>
                            <div class="uk-panel-badge uk-badge uk-badge-success">OK</div>
                        <?php else: ?>
                            <div class="uk-panel-badge uk-badge uk-badge-danger">Error</div>
                            <div class="uk-panel-content"><?=$panel['content'];?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>