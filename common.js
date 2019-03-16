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
    
    this.xhr.open("POST", this.url + "/user", true);
    this.xhr.setRequestHeader("Content-Type", "application/json");
    this.xhr.send(JSON.stringify({"userName": userName}));
    
    this.xhr.onreadystatechange = function () {
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            var jsonObj = JSON.parse(this.xhr.responseText);
            this.userId = jsonObj["user_id"];
            alert(
                this.xhr.responseText
                );
        }
    }.bind(this);
}

SingleClient.prototype.pushMessageByUserName = function(userName, msg){
    
    // this.xhr.open("POST", this.url + "/user/" + userId, true);
    this.xhr.open("POST", this.url + "/user/1", true);
    this.xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");
    data = JSON.stringify({"userName": userName, "msg": msg});
    alert(this.url + "/user/1");
    this.xhr.send(data);
    this.xhr.onreadystatechange = function () {
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            var jsonObj = JSON.parse(this.xhr.responseText);
        }
    }.bind(this);
}

// singleClient = new SingleClient();
// function getOnline() {    
//     messages = singleClient.getMessagesByUserId(1);
// }

$( "#sendMessage" ).click(function(){
    
    singleClient = new SingleClient();
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
        singleClient = new SingleClient();
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