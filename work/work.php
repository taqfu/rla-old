<?php

include ("../config.php");
$connection = new PDO("mysql:host=localhost;dbname=rla", "root", "");

switch (filter_input(INPUT_POST, "function_to_be_called", FILTER_SANITIZE_STRING)) {
    case "associate":
        associate(filter_input(INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'action_id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "cancel_work":
        cancel_work(filter_input(INPUT_POST, 'action_id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "change_work":
        change_work(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'work', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "change_work_status_of_action":
        change_work_status_of_action(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'work', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "create_action":
        create_action(filter_input(INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING), filter_input(INPUT_POST, 'reference', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "create_new_action":
        create_new_action(filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING));
        break;
    case "create_work":
        create_work(filter_input(INPUT_POST, 'action_id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "delete_top_action":
        delete_top_action(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "delete_action":
        delete_action(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "display_work_history";
        display_work_history();
        break;
    case "list_actions":
        list_actions(filter_input(INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT));
        break;
    case "list_current_actions":
        list_current_actions();
        break;
    case "list_work":
        list_work(filter_input(INPUT_POST, 'work', FILTER_SANITIZE_NUMBER_INT));
        break;
}

function associate($achievement_id, $action_id) {
    global $connection;
    $action = fetch_action($action_id);
    if ($action->achievement_id == 0) {
        $statement = $connection->prepare("insert into actions (name, work, achievement_id, reference) values('" . $action->name . "', $action->work, ?, 0)");
        $statement->bindValue(1, $achievement_id, PDO::PARAM_INT);
        $statement->execute();
        $statement = $connection->prepare("update actions set active=0 where id=?");
        $statement->bindValue(1, $action_id, PDO::PARAM_INT);
        $statement->execute();
    } else {
        $statement = $connection->prepare("insert into actions (name, work, achievement_id, reference) values('" . $action->name . "', $action->work, ?, ?)");
        $statement->bindValue(1, $achievement_id, PDO::PARAM_INT);
        $statement->bindValue(2, $action_id, PDO::PARAM_INT);
        $statement->execute();
    }
}

function cancel_work($action_id) {
    echo $achievement_id;
    global $connection;
    $statement = $connection->prepare("update work set active=0 where active=1 and action_id=? order by created desc limit 1");
    $statement->bindValue(1, $action_id, PDO::PARAM_INT);
    $statement->execute();
}

function change_work($id, $work) {
    //echo "$id $work";
    global $connection;
    $statement = $connection->prepare("update achievements set work=? where id=?");
    $statement->bindvalue(1, $work, PDO::PARAM_INT);
    $statement->bindvalue(2, $id, PDO::PARAM_INT);
    $statement->execute();
}

function change_work_status_of_action($id, $work) {
    global $connection;
    $statement = $connection->prepare("update actions set work=? where active=1 and (id=? or reference=?)");
    $statement->bindvalue(1, $work, PDO::PARAM_INT);
    $statement->bindvalue(2, $id, PDO::PARAM_INT);
    $statement->bindvalue(3, $id, PDO::PARAM_INT);
    $statement->execute();
}

function create_action($achievement_id, $action, $reference) {
    // echo $achievement_id + " " +  $action;
    global $connection;
    if ($reference != 0) {
        $action = fetch_action($reference)->name;
    }
    // echo "insert into actions(achievement_id, name, reference) values ($achievement_id, $action, $reference)";
    $statement = $connection->prepare("insert into actions(achievement_id, name, reference) values (?, ?, ?)");
    $statement->bindValue(1, $achievement_id, PDO::PARAM_INT);
    $statement->bindValue(2, $action, PDO::PARAM_STR);
    $statement->bindValue(3, $reference, PDO::PARAM_INT);
    $statement->execute();
}

function create_work($action_id) {
    global $connection;
    $statement = $connection->prepare("insert into work (action_id) values (?)");
    $statement->bindValue(1, $action_id, PDO::PARAM_INT);
    $statement->execute();
}

function create_new_action($action) {
    global $connection;
    $statement = $connection->prepare("insert into actions (name, achievement_id) values (?, 0)");
    $statement->bindValue(1, $action, PDO::PARAM_STR);
    $statement->execute();
}

function delete_action($id) {
    global $connection;
    $action = fetch_action($id);
    $statement = $connection->query("select count(*) from actions where active=1 and (id=$action->id or reference=$action->id)");
    $statement->execute();
    $num_of_achievements = $statement->fetchColumn();
    if ($num_of_achievements == 1) {
        $connection->exec("insert into actions (name, achievement_id, reference) values ('$action->name', 0, 0)");
        $connection->exec("update actions set active=0 where id=$id");
    } else if ($num_of_achievements > 1) {
        if ($action->reference == 0) {
            $statement = $connection->query("select id from actions where active=1 and reference=$action->id limit 1");
            $next_action_id = $statement->fetchColumn();
            $connection->exec("update actions set active=0 where id=$id");
            $connection->exec("update actions set reference=0 where id=$next_action_id");
            $connection->exec("update actions set reference=$next_action_id where reference=$id");
        } else {
            $statement = $connection->prepare("update actions set active=0 where id=?");
            $statement->bindValue(1, $id, PDO::PARAM_INT);
            $statement->execute();
        }
    } else {
        //ERROR
    }
}

function delete_top_action($id) {
    global $connection;
    $statement = $connection->prepare("update actions set active=0 where id=?");
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
}

function display_achievement($achievement) {
    global $connection;

    echo "<div style='margin-left:32px;border-left:1px solid black;border-top:1px solid black;padding:8px;'>";


    echo "<div><div> <a style='color:black;text-decoration:none;' href='" . SITE_ROOT . "/?rla=$achievement->id';\">$achievement->name</a>";

    echo "</div><div>";
    if ($achievement->work != 2 && $achievement->parent == 0) {
        echo "<input type='button' value='Daily' onclick=\"ChangeWork($achievement->id, 2)\"/>";
    }
    if ($achievement->work != 3 && $achievement->parent == 0) {
        echo "<input type='button' value='Weekly' onclick=\"ChangeWork($achievement->id, 3)\" />";
    }
    if ($achievement->work != 4 && $achievement->parent == 0) {
        echo "<input type='button' value='Monthly' onclick=\"ChangeWork($achievement->id, 4)\"  />";
    }
    /* if ($achievement->parent == 0) {
      echo "</div>";
      } */
    echo "</div>
            <div>";
    $statement = $connection->query("select * from actions where achievement_id=$achievement->id and active=1 order by name");
    $statement->execute();
    while ($action = $statement->fetchObject()) {
        $completed = has_it_been_worked_on($action->id, $achievement->work);
        echo "<div>";
        if ($achievement->work != 1) {
            if ($completed) {
                echo "<input type='button' value='X' style='color:red' onclick=\"CancelWork($action->id)\" />";
            } else {
                echo "<input type='button' value='&#10004;' onclick=\"CreateWork($action->id)\" />";
            }
            echo "$action->name </div>";
        }
    }

    echo "</div></div>";

    $statement = $connection->query("select count(*) from achievements where parent=$achievement->id and active=1");
    $statement->execute();
    if ($statement->fetchColumn() > 0) {
        $statement = $connection->query("select id from achievements where parent=$achievement->id and active=1");
        $statement->execute();
        while ($child_id = $statement->fetchColumn()) {
            display_achievement(fetch_achievement($child_id));
        }
    }
    echo "</div>";
}

function fetch_achievement($id) {
    global $connection;
    $statement = $connection->prepare("select * from achievements where id=?");
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchObject();
}

function fetch_action($id) {
    global $connection;
    $statement = $connection->prepare("select * from actions where id=?");
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchObject();
}

function display_new_action_options($id) {
    global $connection;
    $query = "select * from achievements where active=1 and id not in (select achievement_id from actions where active=1 and (id=? or reference=?)) order by name";
    $statement = $connection->prepare($query);
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->bindValue(2, $id, PDO::PARAM_INT);
    $statement->execute();
    while ($achievement = $statement->fetchObject()) {
        echo "<option value='$achievement->id'>$achievement->name</option>";
    }
}

function list_achievements_for_action($id) {
    global $connection;
    $query = "select actions.id, achievements.name from achievements inner join actions on achievements.id = actions.achievement_id 
        where achievements.active=1 and actions.active=1 and (actions.id=? or actions.reference=?) order by achievements.name";
    $statement = $connection->prepare($query);
    $statement->bindValue(1, $id, PDO::PARAM_INT);
    $statement->bindValue(2, $id, PDO::PARAM_INT);
    $statement->execute();

    while ($result = $statement->fetch()) {
        echo "<div><input type='button' value='X' onclick=\"DeleteAction($result[0], false)\"/>$result[1]</div>";
    }
}

function list_current_actions() {
    global $connection;
    $statement = $connection->query("select * from actions where reference=0 and active=1");
    $statement->execute();
    echo "<option></option>";
    while ($action = $statement->fetchObject()) {
        echo "<option value='$action->id'>$action->name</option>";
    }
}

function list_actions($achievement_id) {
    global $connection;
    $statement = $connection->prepare("select * from actions where active=1 and achievement_id=? order by name");
    $statement->bindValue(1, $achievement_id, PDO::PARAM_INT);
    $statement->execute();
    while ($action = $statement->fetchObject()) {
        echo "<div>
        <input type='button' value='X' onclick=\"DeleteAction($action->id, $action->achievement_id)\" />
        $action->name</div>";
    }
}

function list_work($work) {
    global $connection;
    $statement = $connection->prepare("select * from actions where active=1 and work=? and reference=0 order by name");
    $statement->bindValue(1, $work, PDO::PARAM_INT);
    $statement->execute();
    echo $work;
    while ($action = $statement->fetchObject()) {
        echo "  <div>
                    <input type='button' value='X' onclick=\"DeleteAction($action->id, true);\"/>
                </div>
                <div style='margin-left:20px;'>
                    <div style='cursor:pointer; ";
        if ($action->work > 0) {
           
            if (has_it_been_worked_on($action->id)) {
                echo "text-decoration:line-through;' title='Cancel work'  onmouseover=\"$(this).css('text-decoration', 'none');\"  onmouseleave=\"$(this).css('text-decoration', 'line-through');\" onclick=\"cancelWork($action->id);\"";
            } else {
                echo "color:green;' onmouseover=\"$(this).css('text-decoration', 'line-through');\" onmouseleave=\"$(this).css('text-decoration', 'none');\" onclick=\"createWork($action->id);\"";
            }
        } else {
            echo "'";
            
        }
        echo "  >$action->name</div>                           
                
                    <input id='show_action_options$action->id' type='button' value='+' style=''
                        onclick=\" $('#action_options$action->id').show();$('#show_action_options$action->id').hide();\"/>
                <div id='action_options$action->id' style='display:none'><div>
                    <input type='button' value='-' 
                        onclick=\" $('#action_options$action->id').hide();$('#show_action_options$action->id').show();\" \>";

        if ($work == 0) {
            echo "<input type='button' value='On'  onclick=\"changeWorkStatusOfAction($action->id, 1);\" />";
        } else {
            echo "<input type='button' value='Off' onclick=\"changeWorkStatusOfAction($action->id, 0);\"  />";
            if ($work != 1) {
                echo "<input type='button' value='Unassign' onclick=\"changeWorkStatusOfAction($action->id, 1);\"  />";
            }
            if ($work != 2) {
                echo "<input type='button' value='Daily' onclick=\"changeWorkStatusOfAction($action->id, 2);\"  />";
            }
            if ($work != 3) {
                echo "<input type='button' value='Weekly' onclick=\"changeWorkStatusOfAction($action->id, 3);\"  />";
            }
            if ($work != 4) {
                echo "<input type='button' value='Monthly' onclick=\"changeWorkStatusOfAction($action->id, 4);\" />";
            }
        }

        echo "</div><div><select id='new_achievement_for_action$action->id'>
              <option value=''>Please select an achievement to associate with this action here.</option>";
        display_new_action_options($action->id);
        echo "</select><input type='button' value='Associate' 
            onclick=\"associateAchievementWithAction($('#new_achievement_for_action$action->id').val(), $action->id);\"/></div>
              <div id='list_of_achievements_for_action$action->id'>";
        list_achievements_for_action($action->id);
        echo "</div>
              </div>";
    }
}

function has_it_been_worked_on($action_id) {
    global $connection;
    $action = fetch_action($action_id);
    switch ($action->work) {
        case 1:
            $time_interval = "and work.updated=0";
            break;
        case 2:
            $time_interval = "and work.created>=now()-interval 1 day";
            break;
        case 3:
            $time_interval = "and work.created>=now()-interval 7 day";
            break;
        case 4:
            $time_interval = "and work.created>=now()-interval 30 day";
            break;
    }
    $statement = $connection->prepare("select count(*) from actions inner join work on actions.id=work.action_id 
        where work.active=1 $time_interval and actions.id=? limit 1");
    $statement->bindValue(1, $action_id, PDO::PARAM_INT);
    $statement->execute();
    $work_created = $statement->fetchColumn();
    return $work_created;
}

function display_work_history() {
    global $connection;
    $statement = $connection->query("select * from work order by created desc");
    $statement->execute();
    $today = 0;
    $last_time = 0;
    while ($work = $statement->fetchObject()) {
        $action = fetch_action($work->action_id);
        $achievement = fetch_achievement($action->achievement_id);

        if ($today != date("m/d/y", strtotime($work->created))) {
            echo "<h2>" . date("m/d/y", strtotime($work->created)) . "</h2>";
            $today = date("m/d/y", strtotime($work->created));
        }
        if ($last_time != date("H:i", strtotime($work->created))) {
            echo "<div>" . date("H:i", strtotime($work->created)) . "</div>";
            $last_time = date("H:i", strtotime($work->created));
        }

        //var_dump (strtotime($work->updated));
        echo "<div>Finished";
        switch ($action->work) {
            case 2:
                echo " daily ";
                break;
            case 3:
                echo " weekly ";
                break;
            case 4:
                echo " monthly ";
                break;
        }
        echo "work on \"$action->name\"";

        if (strtotime($work->updated)) {
            echo " then cancelled at " . date("H:i:s", strtotime($work->created));
        }
        echo "</div>";
    }
}