
function createAction(achievement_id, action) {
    console.log(achievement_id);
    console.log(action);
    if (!testIfVariableIsNumber(achievement_id, "achievement_id") 
            || !testIfVariableIsString(action, "action") || !testStringForMaxLength(action, 255, "action") ){
       return;
    }
    $.ajax({
        method: "POST",
        url: "/rla/php/ajax.php",
        data: {function_to_be_called: "create_action", achievement_id: achievement_id, action: action}
    })
            .done(function (result) {
                console.log(result);
                listAllActions(achievement_id);
                //still not displaying properly
            });

}



function deleteAction(id, achievement_id) {
    if (!testIfVariableIsNumber(id, "id") ||!testIfVariableIsNumber(achievement_id, "achievement_id")){
        return;        
    }
    if (window.confirm("Are you sure you want to delete this action?")) {
        $.ajax({
            method: "POST",
            url: "/rla/php/ajax.php",
            data: {function_to_be_called: "delete_action", id: id}
        })
                .done(function (result) {
                    listAllActions(achievement_id);
                });
    }
}


