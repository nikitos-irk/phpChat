function SingleClient(userId=0) {
        if (userId != 0){
            this.userId = userId;
        }
        this.xhr = new XMLHttpRequest();
        this.url = "http://localhost:8000";
}

SingleClient.prototype.getMessagesByUserId = function(userId){
    $.ajax(this.url + "/user/" + userId).done(function(data) {
        var resp  = JSON.parse(data);
        for (var key in resp["messages"]){
            msg = resp["messages"][key];
            $("#allMessages").append(
                msg["user_name"] + ": " + msg["message"] + "\n"
            );
        }
    });
}

SingleClient.prototype.UserLogin = function(userName){
    var data = {};
    data.userName = userName;
    $.ajax({
        type: 'POST',
        url: this.url + "/user",
        data: data,
        dataType: 'json',
        success: function(data) {
            window.location.href = 'gui/chat.html?userId=' + data["user_id"];
       }
    });
}

SingleClient.prototype.pushMessageByUserName = function(userName, msg){
    var data = {};
    data.userName = userName;
    data.msg = msg;
    $.ajax({
        type: 'POST',
        url: this.url + "/user/" + this.userId,
        data: data,
        dataType: 'json'
    });
}


var url = new URL(window.location.href);
singleClient = new SingleClient(url.searchParams.get("userId"));

$('#makeLogin').click(function() {
    singleClient = new SingleClient();
    singleClient.UserLogin($("#uName").val());
});

$( "#sendMessage" ).click(function(){
    fullMsg = $("#msg").val();

    if (fullMsg.charAt(0) != '@'){
        alert("Correct syntax: @username some message");
    } else {
        userName = fullMsg.substr(1, fullMsg.indexOf(' ') - 1);
        msg = fullMsg.substr(fullMsg.indexOf(' ') + 1);
        singleClient.pushMessageByUserName(userName, msg);
    }
    $("#msg").val("");
});

function process(){
    $( "#allMessages" ).ready(function() {
        messages = singleClient.getMessagesByUserId(singleClient.userId);
    });
    setTimeout(function(){ process(); },1000);
}
process();