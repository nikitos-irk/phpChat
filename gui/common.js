function SingleClient(userId=0) {
    // constructor(userId = 0) {
        if (userId != 0){
            this.userId = userId;
        }
        this.xhr = new XMLHttpRequest();
        this.url = "http://localhost:8000";
    // }
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
    var that = this;
    $.ajax({
        type: 'POST',
        url: that.url + "/user",
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
        // url: that.url + "/user/" + window.singleClient.userId,
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
        messages = singleClient.getMessagesByUserId(singleClient.userId,
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