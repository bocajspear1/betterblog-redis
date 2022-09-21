<?php include "include/head.php" ?>


<?php
    $action = "pages";

    if (array_key_exists("action", $_POST)) {
        $action = $_POST['action'];
        
        if ($action == "config") {
            $new_data = array();
            foreach ($_POST as $key => $val) {
                if ($key != "action") {
                    $new_data[$key] = $val;
                }
            }
            $new_json = json_encode($new_data, JSON_PRETTY_PRINT);
            file_put_contents("./config.json", $new_json);
        } elseif ($action == "upload") {

        }
    }

    
    if (array_key_exists("action", $_GET)) {
        $action = $_GET['action'];
        if ($action == "download") {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"" . basename($_GET['path']) . "\""); 
            readfile($_GET['path']); 
            $action = "files";
        }
    }
?>

<?php include "include/database.php" ?>

<?php

function get_login() {
    header('WWW-Authenticate: Basic realm="BetterBlog"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Not authenticated, check your config.json file for backup user/password!';
    exit;
}

if ((!array_key_exists('logged_in_user', $_SESSION)) || $_SESSION['logged_in_user'] == "") {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        get_login();
    } else {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $auth = false;
        if ($DB_AVAILABLE) {
            
        }

        if ($username == $_CONFIG->backup_user && $password == $_CONFIG->backup_password) {
            $auth = true;
        }

        if ($auth == true) {
            $_SESSION['logged_in_user'] = $username;
        } else {
            $_SESSION['logged_in_user'] = "";
            get_login();
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
        <title>Admin - Betterblog</title>
        <link rel="stylesheet" href="css/bulma.min.css">
</head>

<body>

    <div class="container">
        <div class="columns">
            <div class="column is-3 ">
                <aside class="menu is-hidden-mobile">
                    <p class="menu-label">
                        <strong>Hello, <?php echo $_SESSION['logged_in_user']; ?></strong>
                    </p>
                    <p class="menu-label">
                        Content
                    </p>
                    <ul class="menu-list">
                        <li><a <?php if ($action=="pages") { echo 'class="is-active"';} ?> href="admin.php?action=pages">Pages</a></li>
                        <li><a <?php if ($action=="posts") { echo 'class="is-active"';} ?> href="admin.php?action=posts">Posts</a></li>
                    </ul>
                    <p class="menu-label">
                        Administration
                    </p>
                    <ul class="menu-list">
                        <li><a <?php if ($action=="users") { echo 'class="is-active"';} ?> href="admin.php?action=users">Users</a></li>
                        <li><a <?php if ($action=="config") { echo 'class="is-active"';} ?> href="admin.php?action=config">Configuration</a></li>
                        <li><a <?php if ($action=="files") { echo 'class="is-active"';} ?> href="admin.php?action=files">File Management</a></li>
                        <li><a <?php if ($action=="tools") { echo 'class="is-active"';} ?> href="admin.php?action=tools">Server Tools</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <?php if ($action == "pages"): ?>
                <h2 class="title is-h2">Pages</h2>
                <?php elseif ($action == "posts"): ?>
                <?php elseif ($action == "users"): ?>
                <?php elseif ($action == "config"): ?>
                <h2 class="title is-h2">Configuration</h2>
                    <form action="admin.php" method="post">
                        <?php
                        $config_json = file_get_contents("./config.json");
                        $config_obj = json_decode($config_json);
                        foreach($config_obj as $key => $val) {
                            echo "<div class=\"field\"><label class=\"label\">$key</label><div class=\"control\"><input class=\"input\" type=\"text\" name=\"$key\" value=\"$val\"></div></div>\n";
                        }
                        ?>
                        <div class="field is-grouped">
                            <div class="control">
                                <input type="submit" class="button is-link" value="Submit"/>
                                <input type="hidden" name="action" value="config"/>
                            </div>
                        </div>
                    </form>
                
                
                <?php elseif ($action == "files"): ?>
                <h2 class="title is-h2">Files</h2>
                    
                    <div class="content">
                    <a class="button" href="admin.php?action=files&resetdir=1">Reset</a>
                    <?php
                        if (!array_key_exists("dir", $_SESSION)) {
                            $_SESSION['dir'] = getcwd();
                        }
                        if (array_key_exists("dir", $_GET)) {
                            $realpath = realpath($_SESSION['dir'] . "/" . $_GET['dir']);
                            if (file_exists($realpath)) {
                                $_SESSION['dir'] = $realpath;
                            }
                        }
                        if (array_key_exists("resetdir", $_GET)) {
                            $_SESSION['dir'] = getcwd();
                        }
                        $listing = scandir($_SESSION['dir']);
                        echo "<ul>";
                        foreach($listing as $item) {
                            $fullpath = $_SESSION['dir'] . "/" . $item;
                            if (is_dir($fullpath)) {
                                echo "<li><a href=\"admin.php?action=files&dir=$item\">$item/</a></li>\n";
                            } else {
                                echo "<li>$item&nbsp;<a href=\"admin.php?action=download&path=$fullpath\">Download</a>&nbsp;<a href=\"admin.php?action=delete&path=$fullpath\">Delete</a></li>\n";
                            }
                            
                        }
                        echo "</ul>";
                    ?>
                    </div>
                <?php elseif ($action == "tools"): ?>
                <h2 class="title is-h2">Tools</h2>
                <?php endif?>
            </div>
        </div>
    </div>
    <script async type="text/javascript" src="../js/bulma.js"></script>
</body>

</html>