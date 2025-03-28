<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Change this if you have set a different username
$password = ""; // Change this if you have set a password
$dbname = "acadmate"; // Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch posts from the database
$sql_posts = "SELECT * FROM post ORDER BY created_at DESC";
$result_posts = $conn->query($sql_posts);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are filled
    if (empty($_POST["title"]) || empty($_POST["author"]) || empty($_POST["body"])) {
    } else {
        $title = $_POST["title"];
        $author = $_POST["author"];
        $body = $_POST["body"];
        $sql = "INSERT INTO post (title, author, body, created_at) VALUES ('$title', '$author', '$body', NOW())";
        if ($conn->query($sql) === TRUE) {
            $success_message = "New post created successfully";
            // Fetch the newly added post
            $result_posts = $conn->query($sql_posts);
            $result_posts->data_seek(0);
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $sql_posts = "SELECT * FROM post ORDER BY created_at DESC";
    $result_posts = $conn->query($sql_posts);
    $result_posts->data_seek(0);
}

if (isset($_POST["comment_body"]) && isset($_POST["post_id"])) {
    $comment_body = $_POST["comment_body"];
    $post_id = $_POST["post_id"];
    // Check if both comment_body and post_id are not empty
    if (!empty($comment_body) && !empty($post_id)) {
        // Assuming $conn is your database connection
        $sql = "INSERT INTO comment (post_id, body, created_at) VALUES ('$post_id', '$comment_body', NOW())";
        if ($conn->query($sql) === TRUE) {
            echo "New comment created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
    }
} else {
    ;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Discussion Forum</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="./forum.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header>
        <?php include './header.php'; ?>
    </header>
<div class="container">
  <div class="response-group">
    <header>
        <h2><strong>Discussion Forum</strong><i class="fa fa-angle-right"></i></h2>
    </header>
    
    <!-- New Post Button -->
    <button class="new-post-button">New Post</button>

    <!-- New Post Popup -->
    <div class="new-post-popup">
      <div class="panel">
        <h2>Create New Post</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author"required>  
            <textarea name="body" placeholder="Description" required></textarea>
            <button type="submit">Submit</button>
          </form>
          
        <button class="cancel">Cancel</button>
      </div>
    </div>

    <?php
    $post_count = 1;
    while ($row_post = $result_posts->fetch_assoc()) {
    ?>
    <div class="response">
        <div class="response__number"><?php echo $post_count++; ?></div>
        <h1 class="response__title"><?php echo $row_post['title']; ?></h1>
        <div class="post-group">
            <div class="post">
                <h3 class="post__author"><?php echo $row_post['author']; ?></h3>
                <p class="post__timestamp"><?php echo $row_post['created_at']; ?></p>
                <p class="post__body"><?php echo $row_post['body']; ?></p>
                <div class="post__actions">
                    <?php
                    // Get the number of likes and dislikes for the post
                    $post_id = $row_post['id'];
                    $sql_likes = "SELECT COUNT(*) AS likes FROM post_likes WHERE post_id = '$post_id' AND is_like = 1";
                    $sql_dislikes = "SELECT COUNT(*) AS dislikes FROM post_likes WHERE post_id = '$post_id' AND is_like = 0";
                    $result_likes = $conn->query($sql_likes);
                    $result_dislikes = $conn->query($sql_dislikes);
                    $likes = $result_likes->fetch_assoc()['likes'];
                    $dislikes = $result_dislikes->fetch_assoc()['dislikes'];
                    ?>
                    <div class="button">
                        <div class="like-dislike-count">
                            <span class="like-count"><?php echo $likes; ?></span>
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <input type="hidden" name="action" value="like">
                                <button type="submit" class="button button--approve">
                                    <i class="fa fa-thumbs-up"></i>
                                    <i class="fa fa-thumbs-up solid"></i>
                                </button>
                            </form>
                            <span class="dislike-count"><?php echo $dislikes; ?></span>
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <input type="hidden" name="action" value="dislike">
                                <button type="submit" class="button button--deny">
                                    <i class="fa fa-thumbs-down"></i>
                                    <i class="fa fa-thumbs-down solid"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <?php
                    // Get the number of comments for the post
                    $sql_comments_count = "SELECT COUNT(*) AS comments FROM comment WHERE post_id = '$post_id'";
                    $result_comments_count = $conn->query($sql_comments_count);
                    $comments_count = $result_comments_count->fetch_assoc()['comments'];
                    ?>
                    <div class="button button--flag">
                        <i class="fa fa-comment-o"></i>
                        <i class="fa fa-comment solid"></i><span class="comment-count"><?php echo $comments_count; ?></span>
                    </div>
                </div>
                <!-- Comments Section -->
                <div class="post__comments">
                    <?php
                    $sql_comments = "SELECT * FROM comment WHERE post_id = '$post_id' ORDER BY created_at DESC";
                    $result_comments = $conn->query($sql_comments);

                    while ($row_comment = $result_comments->fetch_assoc()) {
                    ?>
                        <div class="comment">
                            <p class="comment__timestamp"><?php echo $row_comment['created_at']; ?></p>
                            <p class="comment__body"><b><?php echo $row_comment['body']; ?></b></p>
                        </div>
                    <?php } ?>

                    <!-- Add New Comment Form -->
                    <div class="comment-form">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <textarea name="comment_body" placeholder="Add your comment..." required></textarea>
                            <div class="button button--confirm">
                                <button type="submit">Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End of Comments Section -->
            </div>
        </div>
    </div>
    <?php
    }
    ?>

</div>

<script src="./forum.js"></script>
</body>
</html>

<?php
$conn->close();
?>