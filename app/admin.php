<?php include "include/head.php" ?>
<?php include "include/database.php" ?>

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

        } elseif ($action == "savepage") {
            $page = $_POST['path'];
            $content = $_POST['pagecontent'];
            $title = $_POST['title'];
            if ($page != 'index.php') {
                $new_header = "<?php \$PAGE='$title'; include(\"../include/pageheader.php\");?>";
                $content = $new_header . "\n" . $content;
            }
            file_put_contents("pages/" . $page, $content);

            if ($DB_AVAILABLE) {
                $query = "SELECT * FROM pages WHERE filename='$page'";
                $result = $mysqli->query($query);

                if (!$result) {
                    echo "<div class=\"notification is-danger\">Failed to query: (" . $mysqli->errno . ") " . $mysqli->error . "</div>";
                } else {
                    $query = "";
                    if ($result->num_rows > 0) {
                        $query = "UPDATE pages SET title='$title' WHERE filename='$page'";
                    } else {
                        $query = "INSERT INTO pages (filename, title) VALUES ('$page', '$title')";
                    }
                    $result = $mysqli->query($query);
                }
            }


            $action = 'pages';
        } elseif ($action == "savepost") {
            $post = $_POST['path'];
            $content = $_POST['postcontent'];
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'];
            $author = $_SESSION['logged_in_user'];
            $lastedit = date(DATE_RFC2822);
            $description = "";
            if (array_key_exists('description', $_POST)) {
                $description = $_POST['description'];
            }
            
            $new_header = "<?php \$PAGE='$title';\$SUBTITLE='$subtitle';\$LASTEDIT='$lastedit';\$AUTHOR='$author';include(\"../include/postheader.php\");?>";
            $content = $new_header . "\n" . $content;
            
            file_put_contents("posts/" . $post, $content);

            if ($DB_AVAILABLE) {
                $query = "SELECT * FROM posts WHERE filename='$post'";
                $result = $mysqli->query($query);

                if (!$result) {
                    echo "<div class=\"notification is-danger\">Failed to query: (" . $mysqli->errno . ") " . $mysqli->error . "</div>";
                } else {
                    $query = "";
                    if ($result->num_rows > 0) {
                        $query = "UPDATE posts SET title='$title',subtitle='$subtitle',description='$description',author='$author',last_modified='$lastedit' WHERE filename='$post'";
                    } else {
                        $query = "INSERT INTO posts (filename, title, subtitle, description, author, last_modified) VALUES ('$post', '$title', '$subtitle', '$description', '$author', '$lastedit')";
                    }
                    $result = $mysqli->query($query);
                }
            }


            $action = 'posts';
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
            $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";

            $result = $mysqli->query($query);

            if (!$result) {
                echo "<div class=\"notification is-danger\">Failed to query: (" . $mysqli->errno . ") " . $mysqli->error . "</div>";
            } else {
                if ($result->num_rows > 0) {
                    $auth = true;
                }
            }
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
        <link rel="stylesheet" href="css/all.min.css">
        <link rel="stylesheet" href="css/bulma.min.css">
        <script src="js/ace.js" referrerpolicy="origin"></script>
</head>

<body>
    <div class="container mt-4">
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
                <h2 class="title is-2">Pages</h2>
                <div class="content">
                    
                    <?php if (array_key_exists('page', $_GET)): ?>
                        <h4 class="subtitle is-4">Editing page <?php echo $_GET['page'];?></h4>
                        <?php 
                            $content = "";
                            $title = "";
                            $path = 'pages/' . $_GET['page'];
                            if (file_exists($path)) {
                                $content = file_get_contents($path); 
                            }

                            if ($_GET['page'] != 'index.php' && $content != "") {
                                $content_split = explode("\n", $content, 2);
                                $content = htmlentities($content_split[1], ENT_QUOTES);
                                $first_line = $content_split[0];
                                $first_line = str_replace("<?php", "", $first_line);
                                $first_line = str_replace("?>", "", $first_line);
                                $first_split = explode(";", $first_line);
                                foreach ($first_split as $split_item) {
                                    if (strpos($split_item, "PAGE") === 2) {
                                        $title = str_replace("'", "", explode("=", $split_item)[1]);
                                    }
                                }
                            } elseif ($_GET['page'] == 'index.php') {
                                $content = htmlentities($content);
                                $title = "Index";
                            }
                            

                            
                            if ($DB_AVAILABLE) {

                            }
                        ?>
                        <form action="admin.php" method="post">
                            <div class="field">
                                <label class="label">Title</label>
                                <div class="control">
                                    <input class="input" type="text" name="title" value="<?php echo $title; ?>">
                                </div>
                            </div>
                            <div id="pagecontent" style="height: 500px;"><?php echo $content; ?></div>
                            <div class="field is-grouped mt-3">
                                <div class="control">
                                    <input type="submit" class="button is-link" value="Save Edit"/>
                                    <input type="hidden" name="pagecontent" id="pagecontent-input"> 
                                    <input type="hidden" name="action" value="savepage"> 
                                    <input type="hidden" name="path" value="<?php echo $_GET['page'] ?>"> 
                                </div>
                            </div>
                        </form>
                        <script>
                            var editor = ace.edit("pagecontent");
                            editor.setTheme("ace/theme/monokai");
                            editor.session.setMode("ace/mode/php");
                            // https://stackoverflow.com/questions/6440439/how-do-i-make-a-textarea-an-ace-editor
                            document.getElementById("pagecontent-input").value = editor.getSession().getValue();
                            editor.getSession().on('change', function(){
                                document.getElementById("pagecontent-input").value = editor.getSession().getValue();
                            });
                        </script>

                    <?php else: ?>
                        <ul>
                        <?php 
                            $dir_contents = scandir("./pages");
                            foreach ($dir_contents as $page_item) {
                                if ($page_item == ".." || $page_item == ".") {
                                    continue;
                                }
                                if ($DB_AVAILABLE) {
                                    echo "<li><a href=\"admin.php?action=pages&page=$page_item\">$page_item</a>";
                                } else {
                                    echo "<li><a href=\"admin.php?action=pages&page=$page_item\">$page_item</a>";
                                }

                            }
                        ?>
                        </ul>
                        <form action="admin.php" method="get">
                            <div class="field">
                                <label class="label">New Page</label>
                                <div class="control">
                                    <input class="input" type="text" name="page">
                                </div>
                            </div>
                            <div class="field is-grouped mt-3">
                                <div class="control">
                                    <input type="submit" class="button is-link" value="Create"/>
                                    <input type="hidden" name="action" value="pages"> 
                                </div>
                            </div>
                        </form>
                    
                    <?php endif?>
                </div>
                <?php elseif ($action == "posts"): ?>
                    <h2 class="title is-2">Posts</h2>
                    <?php if (!$DB_AVAILABLE): ?>
                        <div class="notification is-danger">
                        Enable database to get a better post listings and better features!
                        </div>
                    <?php endif?>
                    <div class="content">
                    
                    <?php if (array_key_exists('post', $_GET)): ?>
                        <h4 class="subtitle is-4">Editing post <?php echo $_GET['post'];?></h4>
                        <?php 
                            $content = "";
                            $title = "";
                            $subtitle = "";
                            $description = "";

                            $path = 'posts/' . $_GET['post'];
                            if (file_exists($path)) {
                                $content = file_get_contents($path); 
                            }

                            $content_split = explode("\n", $content, 2);
                            $content = htmlentities($content_split[1], ENT_QUOTES);
                            $first_line = $content_split[0];
                            $first_line = str_replace("<?php", "", $first_line);
                            $first_line = str_replace("?>", "", $first_line);
                            $first_split = explode(";", $first_line);
                            foreach ($first_split as $split_item) {
                                if (strpos($split_item, "PAGE") === 2) {
                                    $title = str_replace("'", "", explode("=", $split_item)[1]);
                                } elseif (strpos($split_item, "SUBTITLE") === 2) {
                                    $subtitle = str_replace("'", "", explode("=", $split_item)[1]);
                                }
                            }
                            

                            if ($DB_AVAILABLE) {

                            }
                        ?>
                        <form action="admin.php" method="post">
                            <div class="field">
                                <label class="label">Title</label>
                                <div class="control">
                                    <input class="input" type="text" name="title" value="<?php echo $title; ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label class="label">Subtitle</label>
                                <div class="control">
                                    <input class="input" type="text" name="subtitle" value="<?php echo $subtitle; ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label class="label">Description</label>
                                <div class="control">
                                    <input class="input" type="text" name="description" value="<?php echo $description; ?>">
                                </div>
                            </div>
                            <div id="postcontent" style="height: 500px;"><?php echo $content; ?></div>
                            <div class="field is-grouped mt-3">
                                <div class="control">
                                    <input type="submit" class="button is-link" value="Save Edit"/>
                                    <input type="hidden" name="postcontent" id="postcontent-input"> 
                                    <input type="hidden" name="action" value="savepost"> 
                                    <input type="hidden" name="path" value="<?php echo $_GET['post'] ?>"> 
                                </div>
                            </div>
                        </form>
                        <script>
                            var editor = ace.edit("postcontent");
                            editor.setTheme("ace/theme/monokai");
                            editor.session.setMode("ace/mode/php");
                            // https://stackoverflow.com/questions/6440439/how-do-i-make-a-textarea-an-ace-editor
                            document.getElementById("postcontent-input").value = editor.getSession().getValue();
                            editor.getSession().on('change', function(){
                                document.getElementById("postcontent-input").value = editor.getSession().getValue();
                            });
                        </script>

                    <?php else: ?>
                        <ul>
                        <?php 
                            $dir_contents = scandir("./posts");
                            foreach ($dir_contents as $post_item) {
                                if ($post_item == ".." || $post_item == ".") {
                                    continue;
                                }
                                if ($DB_AVAILABLE) {
                                    echo "<li><a href=\"admin.php?action=posts&post=$post_item\">$post_item</a>";
                                } else {
                                    echo "<li><a href=\"admin.php?action=posts&post=$post_item\">$post_item</a>";
                                }
                            }
                        ?>
                        </ul>
                    
                    <?php endif?>
                </div>
                  
                <?php elseif ($action == "users"): ?>
                    <?php if (!$DB_AVAILABLE): ?>
                        <div class="notification is-danger">
                        A database must be enabled for user management!
                        </div>
                    <?php else: ?>

                    <?php endif?>
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
                                echo "<li>$item&nbsp;&nbsp;<a href=\"admin.php?action=download&path=$fullpath\"><i class=\"fas fa-download\"></i></a>&nbsp;&nbsp;<a href=\"admin.php?action=delete&path=$fullpath\"><i class=\"fas fa-trash\"></i></a></li>\n";
                            }
                            
                        }
                        echo "</ul>";
                    ?>
                    </div>
                <?php elseif ($action == "tools"): ?>
                <h2 class="title is-h2">Tools</h2>
                <?php else:?>
                    <div class="notification is-danger">
                    Invalid action
                    </div>
                <?php endif?>
            </div>
        </div>
    </div>
</body>

</html>