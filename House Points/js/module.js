// JavaScript Document
/*
function awardSave() {
    // save points awarded
    $.ajax({
        url: modpath + "/award_save_ajax.php",
        data: {
            formData: $('#awardForm').serialize()
        },
        type: 'POST',
        success: function(data) {
            console.log(data);
            if (data !== '') {
                $('#msg').html(data);
                $('#submit').prop('disabled', true).css({'background-color': '#cccccc', 'color': 'gray'});
            }
        }
    });
}
*/

function houseSave() {
    // save points awarded
    $.ajax({
        url: modpath + "/house_save_ajax.php",
        data: {
            formData: $('#awardForm').serialize()
        },
        type: 'POST',
        success: function(data) {
            //console.log(data);
            if (data !== '') {
                $('#msg').html(data);
                $('#submit').prop('disabled', true).css({'background-color': '#cccccc', 'color': 'gray'});
            }
        }
    });
}

function showIndividualPoint(studentID) {
    $.ajax({
        url: modpath + "/individual_ajax.php",
        data: {
            studentID: studentID
        },
        type: 'POST',
        dataType: 'JSON',
        success: function(data) {
            console.log(data);
            var points = data.points;
            
            var html = '';
            if (points.length === 0) {
                html = "<p>No points have been awarded to this student</p>";
            } else {
                var total = 0;
                html += "<table style='width:100%;'>";
                    html += "<tr>";
                        html += "<th style='width:15%;'>Date</th>";
                        html += "<th style='width:10%;'>Points</th>";
                        html += "<th style='width:15%;'>Category</th>";
                        html += "<th style='width:30%;'>Reason</th>";
                        html += "<th style='width:20%;'>Awarded By</th>";
                    html += "</tr>";
                    $.each(points, function(i, pt) {
                        total += parseInt(pt.points);
                        html += "<tr>";
                            html += "<td>" + pt.awardedDate + "</td>";
                            html += "<td>" + pt.points + "</td>";
                            html += "<td>" + pt.categoryName + "</td>";
                            html += "<td>" + pt.reason + "</td>";
                            html += "<td>" + pt.teacherName + "</td>";
                        html += "</tr>";
                    });
                    html += "<tr>";
                        html += "<td><strong>Total</strong></td>";
                        html += "<td colspan='4'><strong>" + total + " points</strong></td>";
                    html += "</tr>";
                html += "</table>";
            }
            
            $('#studentPoints').html(html);
        }
    });
}


function managePointStudent(studentID) {
    $.ajax({
        url: modpath + "/individual_ajax.php",
        data: {
            studentID: studentID
        },
        type: 'POST',
        dataType: 'JSON',
        success: function(data) {
            console.log(data);
            var points = data.points;
            
            var html = '';
            if (points.length === 0) {
                html = "<p>No points have been awarded to this student</p>";
            } else {
                var total = 0;
                html += "<table style='width:100%;'>";
                    html += "<tr>";
                        html += "<th style='width:15%;'>Date</th>";
                        html += "<th style='width:10%;'>Points</th>";
                        html += "<th style='width:15%;'>Category</th>";
                        html += "<th style='width:30%;'>Reason</th>";
                        html += "<th style='width:20%;'>Awarded By</th>";
                        html += "<th style='width:10%;'>Action</th>";
                    html += "</tr>";
                    $.each(points, function(i, pt) {
                        total += parseInt(pt.points);
                        html += "<tr>";
                            html += "<td>" + pt.awardedDate + "</td>";
                            html += "<td>" + pt.points + "</td>";
                            html += "<td>" + pt.categoryName + "</td>";
                            html += "<td>" + pt.reason + "</td>";
                            html += "<td>" + pt.teacherName + "</td>";
                            html += "<td>";
                                //html += "<a href='#' class='edit' data-id='" + pt.hpID + "'>Edit</a>&nbsp;&nbsp;"; 
                                html += "<a href='#' class='deleteStudent' data-id='" + pt.hpID + "'>Delete</a>";
                            html += "</td>";
                        html += "</tr>";
                    });
                    
                html += "</table>";
            }
            
            $('#studentPoints').html(html);
            
            
            $('.deleteStudent').click(function() {
                if (confirm("Delete this entry?")) {
                    deletePointStudent(studentID, $(this).data('id'));
                }
            });
        }
    });
}

function managePointHouse(houseID) {
    $.ajax({
        url: modpath + "/house_ajax.php",
        data: {
            houseID: houseID
        },
        type: 'POST',
        dataType: 'JSON',
        success: function(data) {
            console.log(data);
            var points = data.points;
            
            var html = '';
            if (points.length === 0) {
                html = "<p>No points have been awarded to this house</p>";
            } else {
                var total = 0;
                html += "<table style='width:100%;'>";
                    html += "<tr>";
                        html += "<th style='width:15%;'>Date</th>";
                        html += "<th style='width:10%;'>Points</th>";
                        html += "<th style='width:15%;'>Category</th>";
                        html += "<th style='width:30%;'>Reason</th>";
                        html += "<th style='width:20%;'>Awarded By</th>";
                        html += "<th style='width:10%;'>Action</th>";
                    html += "</tr>";
                    $.each(points, function(i, pt) {
                        total += parseInt(pt.points);
                        html += "<tr>";
                            html += "<td>" + pt.awardedDate + "</td>";
                            html += "<td>" + pt.points + "</td>";
                            html += "<td>" + pt.categoryName + "</td>";
                            html += "<td>" + pt.reason + "</td>";
                            html += "<td>" + pt.teacherName + "</td>";
                            html += "<td>";
                                //html += "<a href='#' class='edit' data-id='" + pt.hpID + "'>Edit</a>&nbsp;&nbsp;"; 
                                html += "<a href='#' class='deleteHouse' data-id='" + pt.hpID + "'>Delete</a>";
                            html += "</td>";
                        html += "</tr>";
                    });
                    
                html += "</table>";
            }
            
            $('#housePoints').html(html);
            
            $('.deleteHouse').click(function() {
                if (confirm("Delete this entry?")) {
                    deletePointHouse(houseID, $(this).data('id'));
                }
            });
        }
    });
}

function deletePointHouse(houseID, hpID) {
    $.ajax({
        url: modpath + "/manage_ajax.php",
        data: {
            hpID: hpID,
            action: 'deleteItemHouse'
        },
        type: 'POST',
        //dataType: 'JSON',
        success: function(data) {
            console.log(data);
            managePointHouse(houseID);
        }
    });
}

function deletePointStudent(studentID, hpID) {
    $.ajax({
        url: modpath + "/manage_ajax.php",
        data: {
            hpID: hpID,
            action: 'deleteItemStudent'
        },
        type: 'POST',
        //dataType: 'JSON',
        success: function(data) {
            console.log(data);
            managePointStudent(studentID);
        }
    });
}


function showClassPoint(classID) {    
    $.ajax({
        url: modpath + "/classpoints_ajax.php",
        data: {
            classID: classID
        },
        type: 'POST',
        dataType: 'JSON',
        success: function(data) {
            console.log(data);
            var points = data.points;
            var total;
            
            var html = '';
            html += "<table style='width:100%;'>";
                html += "<tr>";
                    html += "<th style='width:40%;'>Name</th>";
                    html += "<th style='width:25%;'>Points</th>";
                    html += "<th style='width:15%;'>House</th>";
                html += "</tr>";
                $.each(points, function(i, pt) {
                    total = pt.total === null ? 0 : pt.total;
                    html += "<tr>";
                        html += "<td>" + pt.surname + ", " + pt.preferredName + "</td>";
                        html += "<td>" + total + "</td>";
                        html += "<td>" + pt.houseName + "</td>";
                    html += "</tr>";
                });
            html += "</table>";
            
            $('#studentPoints').html(html);
        }
    });
}

function updateCategoryPoints() {
    $.ajax({
        url: modpath + "/category_points_ajax.php",
        data: {
            categoryID: $('#categoryID').val()
        },
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            
            var parent = $('#points').parent();
            $('#points').detach().remove();

            if (data !== null) {
                var points = $('<select/>').attr('name', 'points').attr('id', 'points').attr('class', 'standardWidth');
                $.each(data, function(value, label) {
                    points.append($("<option/>").attr("value", parseInt(value)).text(label));
                });
                parent.append(points);
            } else {
                var points = $('<input type="text" id="points" name="points" value="1" class="standardWidth" maxlength="4" />');
                parent.append(points);
            }
        }
    });
}