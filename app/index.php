<?php include "include/head.php" ?>
<?php include "include/database.php" ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $_CONFIG->site_name; ?> - Betterblog</title>
        <link rel="stylesheet" href="css/bulma.min.css">
    </head>
    <body>
    <?php 
        $page = "index.php";
        $post = "";
        if (array_key_exists("page", $_GET)) {
            $page = $_GET['page'];
        } elseif (array_key_exists("post", $_GET)) {
            $post = $_GET['post'];
            $page = "";
        }
    ?>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <a class="navbar-item" href="#">
                        
                    </a>
                    <span class="navbar-burger burger" data-target="navbarMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                    </span>
                </div>
                <div id="navbarMenu" class="navbar-menu">
                    <div class="navbar-end">
                        <a class="navbar-item is-active" href="index.php">
                            Home
                        </a>
                        <?php 
                            if (!$DB_AVAILABLE) {
                                $dir_contents = scandir("./pages");
                                foreach ($dir_contents as $nav_page) {
                                    if ($nav_page == ".." || $nav_page == "." || $nav_page == "index.php") {
                                        continue;
                                    }
                                    $display = str_replace(".php", "", $nav_page);
                                    $display = str_replace("-", " ", $display);
                                    $display = str_replace("_", " ", $display);
                                    $display = ucwords($display);
                                    echo "<a class=\"navbar-item\" href=\"index.php?page=$nav_page\">$display</a>";
                                }
                                echo '<a class="navbar-item" href="admin.php">Admin</a>';
                            } else {

                            }
                        ?>
                        
                    </div>
                </div>
            </div>
        </nav>
    <!-- END NAV -->
    <?php if ($page == 'index.php'): ?>
        <section class="hero is-info is-medium is-bold">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <h1 class="title"><?php echo $_CONFIG->welcome_text; ?></h1>
                </div>
            </div>
        </section>


    <?php endif ?>


        <div class="container">
            <section class="articles">
                <div class="column is-8 is-offset-2">
                    <?php if ($page == "index.php"): ?>
                        <div class="box">
                            <?php 
                            chdir("./pages");
                            include($page);
                            chdir("../");
                            ?>
                        </div>
                        <?php 
                        
                            if (!$DB_AVAILABLE) {
                                echo '<div class="notification is-danger">';
                                echo "Enable database to get article listing!";
                                echo "</div>";
                            } else {

                            }
                        ?>
                    <?php elseif ($post != ""): ?>
                    <div class="card article">
                        <div class="card-content">
                            <div class="content article-body">
                                <?php 
                                chdir("./posts");
                                include($post);
                                chdir("../");
                                echo "</div>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="box">
                            <?php 
                            chdir("./pages");
                            include($page);
                            chdir("../");
                            ?>
                        </div>
                    <?php endif ?>
                    
                </div>

            </section>
            <!-- END ARTICLE FEED -->
        </div>
    </body>

</html>
