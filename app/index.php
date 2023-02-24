<?php include "include/head.php" ?>
<?php include "include/database.php" ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $_CONFIG->site_name; ?> - Betterblog</title>
        <link rel="stylesheet" href="css/all.min.css">
        <link rel="stylesheet" href="css/bulma.min.css">
    </head>
    <body>
    <?php 
        $page = "index.php";
        $post = "";
        $offset = 0;
        $pagination_size = 5;
        if (array_key_exists("page", $_GET)) {
            $page = $_GET['page'];
        } elseif (array_key_exists("post", $_GET)) {
            $post = $_GET['post'];
            $page = "";
        } elseif (array_key_exists("p", $_GET)) {
            $offset = (int)$_GET['p'];
            if ($offset > 0) {
                $offset -= 1;
            }
        }
    ?>
        <nav class="navbar is-light">
            <div class="container">
                <div class="navbar-brand">
                    <span class="navbar-item" href="#">
                        <i class="fas fa-comments"></i>&nbsp;<?php echo $_CONFIG->site_name; ?>
                    </span>
                    <span class="navbar-burger burger" data-target="navbarMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                    </span>
                </div>
                <div id="navbarMenu" class="navbar-menu">
                    <div class="navbar-end">
                        <a class="navbar-item <?php if ($page == "index.php") { echo "is-active"; } ?>" href="index.php">
                            <i class="fas fa-home"></i>&nbsp;Home
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
                                    // echo $nav_page;
                                    // echo $page;
                                    if ($nav_page == $page) {
                                        echo "<a class=\"navbar-item is-active\" href=\"index.php?page=$nav_page\">$display</a>";
                                    } else {
                                        echo "<a class=\"navbar-item\" href=\"index.php?page=$nav_page\">$display</a>";
                                    }
                                    
                                }
                                
                            } else {
                                $query = "SELECT * FROM pages";
                                $result = $mysqli->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['filename'] == $page) {
                                        echo "<a class=\"navbar-item is-active\" href=\"index.php?page=" . $row['filename'] . "\">" . $row['title'] . "</a>";
                                    } else {
                                        echo "<a class=\"navbar-item\" href=\"index.php?page=" . $row['filename'] . "\">" . $row['title'] . "</a>";
                                    }
                                    
                                }
                            }
                            echo '<a class="navbar-item" href="admin.php"><i class="fas fa-hammer"></i>&nbsp;Admin</a>';
                        ?>
                        
                    </div>
                </div>
            </div>
        </nav>
    <!-- END NAV -->
    <?php if ($page == 'index.php' && $offset < 1): ?>
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
                        <?php if ($offset == 0): ?>
                        <div class="box">
                            <?php 
                            chdir("./pages");
                            include($page);
                            chdir("../");
                            ?>
                        </div>
                        <?php endif ?>
                        <h2 class="title is-2">Posts</h2>
                        <?php 

                            $dir_contents = array();
                            $group_count = 0;

                            if (!$DB_AVAILABLE) {
                                $dir_contents_raw = scandir("./posts");
                                
                                foreach ($dir_contents_raw as $post_item) {
                                    if ($post_item == ".." || $post_item == "." || $post_item == "index.php") {
                                        continue;
                                    }
                                    $display = str_replace(".php", "", $post_item);
                                    $display = str_replace("-", " ", $display);
                                    $display = str_replace("_", " ", $display);
                                    $display = ucwords($display);

                                    $new_item = array("filename" => $post_item, "title" => $display);
                                    array_push($dir_contents, $new_item);
                                }

                                $groups = array_chunk($dir_contents, $pagination_size);
                                $group_count = count($groups);

                                if (count($groups) >= $offset+1) {
                                    $dir_contents = $groups[$offset];
                                } else {
                                    $dir_contents = array();
                                }
                            } else {

                                $result = $mysqli->query("SELECT COUNT(filename) AS file_count FROM posts;");
                                $group_count = (int)($result->fetch_array()["file_count"] / $pagination_size);

                                $query = "SELECT * FROM posts ORDER BY last_modified DESC LIMIT $pagination_size OFFSET " . ($offset * $pagination_size);
                                $result = $mysqli->query($query);
                                $dir_contents = $result->fetch_all(MYSQLI_ASSOC);
                            }   


                            foreach ($dir_contents as $post_item) {
                                
                                if (array_key_exists("description", $post_item)) {
                                    echo '<div class="card article">';
                                    echo '<div class="card-content">';
                                    echo '<div class="media">';
                                    echo '<div class="media-content">';
                                    echo "<p class=\"title is-4\"><a href=\"index.php?post=" . $post_item['filename'] . "\">" . $post_item['title'] . "</a></p>";
                                    echo "<p class=\"subtitle is-5\">" . $post_item['subtitle'] . "</p>";
                                    echo "</div></div>";    
                                    echo "<div class=\"content\">" . $post_item['description'] . "</div>";
                                    echo "</div></div>";
                                } else {
                                    echo '<div class="card article">';
                                    echo '<div class="card-content">';
                                    echo '<div class="media">';
                                    echo '<div class="media-content">';
                                    echo "<p class=\"title is-4\"><a href=\"index.php?post=" . $post_item['filename']  . "\">" . $post_item['title'] . "</a></p>";
                                    echo "<p class=\"subtitle is-5\"></p>";
                                    echo "</div></div>";    
                                    echo "<div class=\"content\"></div>";
                                    echo "</div></div>";
                                }

                            }

                            echo '<div class="box"><nav class="pagination" role="navigation" aria-label="pagination">';
                            echo '<ul class="pagination-list">';
                            for ($i = 0; $i < $group_count; $i+=1) {
                                if ($i == $offset) {
                                    echo '<a class="pagination-link is-current">' . ($i+1) .'</a>';
                                } else {
                                    echo '<a class="pagination-link" href="index.php?p=' . ($i+1) . '">' . ($i+1) .'</a>';
                                }
                            }
                            echo '</ul>
                            </nav></div>';

                            
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
