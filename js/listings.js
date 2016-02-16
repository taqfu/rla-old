function listAchievements(sort_by) {
    if (!testIfVariableIsString(sort_by, "sort_by")){        
        return;
    }
    $.ajax({
        method: "POST",
        url: "/rla/php/listings.php",
        data: {sort_by: sort_by}
    })
            .done(function (result) {
                $("#list_of_achievements").html(result);
            });
}