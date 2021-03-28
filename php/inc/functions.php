<?php

// autant lancer les sessions ici, tous les fichiers ayant besoin de ces fonctions utilisent les sessions de toute façon
session_start();

function login($name, $pass) {

    require __DIR__.'/../data/users.php';
    
    foreach ($users as $id => $user) {
        if ($user['name'] == $name && $user['password'] == $pass) {
            $_SESSION = ['id' => $id, 'messages' => []];
            return true;
        }
    }
    
    return false;
}

function logout() {
    unset($_SESSION['id']);
}

function isLoggedIn() {
    return isset($_SESSION['id']);
}

function getUser($id) {
    require __DIR__.'/../data/users.php';

    return $users[$id];
}

function getMe() {

    return getUser($_SESSION['id']);
}

function redirect($page, $queryString = []) {
    if (file_exists(__DIR__."/../$page.php")) {
        $url = "$page.php";
        if (!empty($queryString)) {
            $url .= "?".http_build_query($queryString);
        }
        header("Location: $url");
        exit;
    } else {
        header("Location: 404.php");
    }
}

function displayFlash($messages) {
    $messages = array_intersect_key(
        $messages,
        ["info" => 0, "warning" => 0, "error" => 0]
    );

    foreach ($messages as $class => $label):
        $fullText = [
            'nologin' => 'Identifiants incorrects',
            'unauthorized' => 'Accès interdit',
            'loggedout' => 'Déconnexion réussie'
        ][$label];
        ?>
        <p class="<?=$class?>"><?=$fullText?></p>
        <?php
    endforeach;
}

/* version json */
define('MESSAGES_FILE', __DIR__."/../data/messages.json");

function getMessages() {
    return json_decode(file_get_contents(MESSAGES_FILE), true);
}

function setMessages($messages) {
    file_put_contents(MESSAGES_FILE, json_encode($messages));
}

function getMessage($messageId) {
    return getMessages()[$messageId];
}

function addMessage($message, $date, $user) {
    // on génère un id relativement unique
    $id = base64_encode($user['name']).$date;

    $messages = getMessages();
    $messages[$id] = [
        'body' => $message,
        'date' => $date,
        'user' => $user,
        'comments' => []
    ];

    setMessages($messages);
}

function addCommentToMessage($messageId, $comment, $date, $user) {

    $messages = getMessages();
    $messages[$messageId]['comments'][] = [
        'body' => $comment,
        'date' => $date,
        'user' => $user
    ];

    setMessages($messages);
}

/* version session
function getMessages() {

    return $_SESSION['messages'];
}

function getMessage($messageId) {
    return $_SESSION['messages'][$messageId];
}

function addMessage($message, $date, $user) {

    // on génère un id relativement unique
    $id = base64_encode($user['name']).$date;

    $_SESSION['messages'][$id] = [
        'body' => $message,
        'date' => $date,
        'user' => $user,
        'comments' => []
    ];
}

function addCommentToMessage($messageId, $comment, $date, $user) {

    $_SESSION['messages'][$messageId]['comments'][] = [
        'body' => $comment,
        'date' => $date,
        'user' => $user
    ];
    
}
*/

// HR = Human Readable
function displayHRDate($timestamp) {
    setlocale(LC_TIME, 'fr_FR.utf8');
    return strftime('le %e %B %Y à %H:%M', $timestamp);
}