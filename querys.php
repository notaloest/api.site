<?php
$connect = $connect = mysqli_connect('localhost','root','root','sitedb');

function login ($connect, $data) {
    $login = $data['login'];
    $pass = $data['pass'];
    $hash = hash('sha256', $pass);

    $user = mysqli_query($connect, "SELECT `stage`, `id` FROM `users` WHERE `login` = '$login' AND `pass` = '$hash'");

    $userfd = mysqli_fetch_assoc($user);

    $stage = $userfd['stage'];
    $id = $userfd['id'];
    $tocken = bin2hex(random_bytes(16));

    if (isset($user)) {
        mysqli_query($connect, "UPDATE user SET tocken = '$tocken' WHERE `login` = '$login' AND `pass` = '$hash'");
        $userdata = [
            "stage" => $stage,
            "id" => $id,
            "tocken" => $tocken
        ];

        echo json_encode($userdata);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "err_auth"
        ];

        echo json_encode($res);
    }
}
function signup ($connect, $data) {
    $login = $data['login'];
    $pass = $data['pass'];

    $c_login = mysqli_fetch_assoc(mysqli_query($connect, "SELECT login FROM user WHERE login = '$login'"))['login'];

    if ($login <> $c_login) {
        $hash = hash('sha256', $pass);

        mysqli_query($connect, "INSERT INTO user (id, login, password, stage, tocken) VALUES (NULL, '$login', '$hash', 'U', NULL)");

        http_response_code(201);

        $res = [
            "status" => true,
            "messege" => "Account is created"
        ];

        echo json_encode($res);
    } else {
        http_response_code(201);

        $res = [
            "status" => false,
            "messege" => "This login already exists"
        ];

        echo json_encode($res);
    }
}

function logout ($connect, $data) {
    $id = $data['idu'];
    mysqli_query($connect, "UPDATE user SET tocken = NULL WHERE id = '$id'");

    http_response_code(200);

    $res = [
        "status" => true,
        "messege" => "Logout"
    ];

    echo json_encode($res);
}
function addtitle ($connect, $data) {
    $token = $data['tocken'];
    $mess = $data['body'];
    $idu = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM user WHERE tocken='$token'"))["id"];
    $stage =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT stage FROM user WHERE tocken='$token'"))["stage"];
    if ($stage === 'A') {
        if ($chek === $idu) {
            mysqli_query($connect, "INSERT INTO mess (`id`, `user`, `body`) VALUES (NULL, '$idu', '$mess')");

            http_response_code(201);

            $res = [
                "status" => true,
                "mess_id" => mysqli_insert_id($connect)
            ];

            echo json_encode($res);
        } else {
            http_response_code(401);

            $res = [
                "status" => false,
                "messege" => "Unauthorized"
            ];

            echo json_encode($res);
        }
    }
}
function deltitle ($connect, $data) {
    $token = $data['tocken'];
    $idu = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE tocken='$token'"))["id"];

    if ($chek === $idu) {
        mysqli_query($connect, "DELETE FROM mess WHERE id = '$id' AND user = '$idu'");

        http_response_code(200);

        $res = [
            "status" => true,
            "messege" => "Messege is deleted"
        ];

        echo json_encode($res);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "Unauthorized"
        ];

        echo json_encode($res);
    }
}
function vewtitle ($connect, $data) {}
function chekstatus ($connect, $data) {}