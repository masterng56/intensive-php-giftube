<?php
require_once 'functions.php';

$content = '';
$categories = [];

$link = mysqli_connect("localhost", "root", "", "giftube");

if (!$link) {
    $error = mysqli_connect_error();
    show_error($content, $error);
}
else {
    $sql = 'SELECT `id`, `name` FROM categories';
    $result = mysqli_query($link, $sql);

    if ($result) {
        $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    else {
        $error = mysqli_error($link);
        show_error($content, $error);
    }

    $content = include_template('add.php', ['categories' => $categories]);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $gif = $_POST['gif'];

        $filename = uniqid() . '.gif';
        $gif['path'] = $filename;
        move_uploaded_file($_FILES['gif_img']['tmp_name'], 'uploads/' . $filename);

        /* BEGIN STATE 01 */
        $sql = 'INSERT INTO gifs (dt_add, category_id, user_id, title, description, path) VALUES (NOW(), ?, 1, ?, ?, ?)';
        $stmt = mysqli_prepare($link, $sql);
        /* END STATE 01 */

        /* BEGIN STATE 02 */
        mysqli_stmt_bind_param($stmt, 'isss', $gif['category'], $gif['title'], $gif['description'], $gif['path']);
        $res = mysqli_stmt_execute($stmt);
        /* END STATE 02 */

        /* BEGIN STATE 03 */
        if ($res) {
            $gif_id = mysqli_insert_id($link);

            header("Location: gif.php?id=" . $gif_id);
        }
        /* END STATE 03 */
        /* BEGIN STATE 04 */
        else {
            $content = include_template('error.php', ['error' => mysqli_error($link)]);
        }
        /* END STATE 04 */
    }
}

print include_template('index.php', ['content' => $content, 'categories' => $categories]);