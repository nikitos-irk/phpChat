function SingleClient() {
    this.xhr = new XMLHttpRequest();
    this.url = "http://localhost:8000";
    this.userId;
}

SingleClient.prototype.getMessagesByUserId = function(userId, callback){
    
    this.xhr.open("GET", this.url + "/user/" + userId, true);
    this.xhr.setRequestHeader("Content-Type", "application/json");
    this.xhr.send(null);
    
    this.xhr.onreadystatechange = function () {
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            if (typeof callback === "function") {
                callback.apply(this, [this.xhr.responseText]);
            }
        }
    }.bind(this);
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
            this.userId = data["user_id"];
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

singleClient = new SingleClient();

$('#makeLogin').click(function() {
    singleClient.UserLogin($("#uName").val());
    window.location.href = 'gui/chat.html';
    // singleClient = new SingleClient();
    singleClient.userId = 1;
    // alert(singleClient.userId);
});

$( "#sendMessage" ).click(function(){
    
    // singleClient = new SingleClient();
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
        // singleClient = new SingleClient();
        messages = singleClient.getMessagesByUserId(2,
            function (someVal) {
                var resp  = JSON.parse(someVal);
                for (var key in resp["messages"]){
                    msg = resp["messages"][key];
                    $("#allMessages").append(
                        msg["user_name"] + ": " + msg["message"] + "\n"
                    );
                }
            }
        );
    });
    
    setTimeout(function(){ process(); },1000);
}
process();