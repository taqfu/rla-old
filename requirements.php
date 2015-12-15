<?php

$connection = new PDO("mysql:host=localhost;dbname=rla", "root", "");
switch ($_POST['function_to_be_called']) {
    case "list_requirements":
        list_requirements($_POST['id'], $_POST['type']);
        break;
    case "create":
        create($_POST['required_for'], $_POST['required_by']);
        break;
    case "delete":
        delete($_POST['id']);
        break;
    case "list_new":
        list_new_requirements($_POST['id']);
        break;
}

function create($for, $by) {
    global $connection;
    //echo "select count(*) from requirements where active=1 and required_for=$for and required_by=$by";
    $statement = $connection->prepare ("select count(*) from requirements where active=1 and required_for=? and required_by=?");
    $statement->bindValue(1,$for, PDO::PARAM_INT);
    $statement->bindValue(2,$by, PDO::PARAM_INT);
    $statement->execute();
    if ($statement->fetchColumn()>0){
        echo "0 This is already required.";
    } else {
    $statement = $connection->prepare("insert into requirements (required_for, required_by) values (?, ?)");
    $statement->bindValue(1, $for, PDO::PARAM_INT);
    $statement->bindValue(2, $by, PDO::PARAM_INT);
    $statement->execute();
    }
}

function delete($id) {
    global $connection;
    echo "update requirements set active=0 where id=$id";
    $statement = $connection->prepare("update requirements set active=0 where id=?");
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
}

function list_new_requirements($id) {
    global $connection;
    $statement = $connection->prepare("select * from achievements where active=1 and parent=0 order by name asc");
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
    while ($achievement = $statement->fetchObject()) {
        echo "<option value='$achievement->id'>$achievement->name</option>";
    }
}

function list_requirements($id, $type) {
    global $connection;
    switch ($type){
        case "for":
            $other_type="by";
            break;
        case "by":
            $other_type="for";
            break;
    }

    $query = "select count(*) from requirements where active=1 and required_$type=?";
    $statement = $connection->prepare($query);
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
    if ($statement->fetchColumn() == 0) {
        echo "None.";
    } else {
        $query="select achievements.id, achievements.name, requirements.id, requirements.required_$type from achievements inner join requirements on achievements.id=requirements.required_$other_type where requirements.active=1 and required_$type=? order by achievements.name";
       
        $statement = $connection->prepare($query);
        $statement->bindValue(1, $id, PDO::PARAM_INT);
        $statement->execute();
        while ($result=$statement->fetch(PDO::FETCH_NUM)){
            $achievement_id=$result[0];
            $achievement_name=$result[1];
            $requirement_id=$result[2];
            $requirement_required=$result[3];

           echo "<div><input type='button' value='X' 
                    onclick=\"DeleteRequirement($requirement_id, $requirement_required);\" />
                  <a href='http://".$_SERVER['SERVER_NAME']."/rla/?rla=$achievement_id'>$achievement_name</a></div>";
 
        }        


    }
}
